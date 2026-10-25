<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Lock and retrieve or create a stock level row for (product, location).
     */
    public function getStockLevel(int $productId, int $locationId, bool $lock = true): StockLevel
    {
        $query = StockLevel::where('product_id', $productId)
            ->where('location_id', $locationId);

        if ($lock) {
            $query->lockForUpdate();
        }

        $stockLevel = $query->first();

        if (! $stockLevel) {
            $stockLevel = StockLevel::create([
                'product_id' => $productId,
                'location_id' => $locationId,
                'quantity' => 0,
                'reserved' => 0,
            ]);

            if ($lock) {
                // Re-query with lock
                $stockLevel = StockLevel::where('id', $stockLevel->id)->lockForUpdate()->first();
            }
        }

        return $stockLevel;
    }

    /**
     * Record receipt of goods into a location (typically warehouse).
     */
    public function recordReceipt(
        Product $product,
        Location $location,
        float $quantity,
        User $user,
        string $reason,
        ?string $lotCode = null,
        ?string $expiryDate = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor que 0.');
        }

        if ($product->tracks_expiry && (empty($lotCode) || empty($expiryDate))) {
            throw new InvalidArgumentException('Este producto requiere número de lote y fecha de caducidad.');
        }

        return DB::transaction(function () use ($product, $location, $quantity, $user, $reason, $lotCode, $expiryDate) {
            $stock = $this->getStockLevel($product->id, $location->id, true);
            $stock->quantity += $quantity;
            $stock->save();

            // Record batch if applicable
            if ($product->tracks_expiry) {
                Batch::create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'lot_code' => $lotCode,
                    'expiry_date' => $expiryDate,
                    'quantity' => $quantity,
                ]);
            }

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'user_id' => $user->id,
                'type' => StockMovement::TYPE_RECEIPT,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            AuditLog::record('stock.receipt', $product, [
                'location' => $location->name,
                'quantity' => $quantity,
                'lot' => $lotCode,
                'reason' => $reason,
            ]);

            return $movement;
        });
    }

    /**
     * Record stock transfer between locations (e.g. Warehouse -> Store).
     */
    public function recordTransfer(
        Product $product,
        Location $fromLocation,
        Location $toLocation,
        float $quantity,
        User $user,
        string $reason
    ): array {
        if ($fromLocation->id === $toLocation->id) {
            throw new InvalidArgumentException('La ubicación de origen y destino deben ser distintas.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('La cantidad a traspasar debe ser mayor que 0.');
        }

        return DB::transaction(function () use ($product, $fromLocation, $toLocation, $quantity, $user, $reason) {
            // Lock rows in a deterministic order by location_id to prevent deadlocks
            $firstLocId = min($fromLocation->id, $toLocation->id);
            $secondLocId = max($fromLocation->id, $toLocation->id);

            $this->getStockLevel($product->id, $firstLocId, true);
            $this->getStockLevel($product->id, $secondLocId, true);

            $sourceStock = $this->getStockLevel($product->id, $fromLocation->id, true);
            $targetStock = $this->getStockLevel($product->id, $toLocation->id, true);

            if ($sourceStock->available < $quantity) {
                throw new InvalidArgumentException('Stock disponible insuficiente en el origen ('.$sourceStock->available.' disponible).');
            }

            // Deduct from source and add to destination
            $sourceStock->quantity -= $quantity;
            $sourceStock->save();

            $targetStock->quantity += $quantity;
            $targetStock->save();

            // Transfer batches FEFO if perishable
            if ($product->tracks_expiry) {
                $this->transferBatchesFefo($product->id, $fromLocation->id, $toLocation->id, $quantity);
            }

            $outMovement = StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $fromLocation->id,
                'user_id' => $user->id,
                'type' => StockMovement::TYPE_TRANSFER_OUT,
                'quantity' => -$quantity,
                'reason' => 'Traspaso hacia '.$toLocation->name.': '.$reason,
            ]);

            $inMovement = StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $toLocation->id,
                'user_id' => $user->id,
                'type' => StockMovement::TYPE_TRANSFER_IN,
                'quantity' => $quantity,
                'reason' => 'Traspaso desde '.$fromLocation->name.': '.$reason,
            ]);

            AuditLog::record('stock.transfer', $product, [
                'from' => $fromLocation->name,
                'to' => $toLocation->name,
                'quantity' => $quantity,
                'reason' => $reason,
            ]);

            return [$outMovement, $inMovement];
        });
    }

    /**
     * Record stock adjustment (positive or negative count correction).
     */
    public function recordAdjustment(
        Product $product,
        Location $location,
        float $quantityDelta,
        User $user,
        string $reason
    ): StockMovement {
        if ($quantityDelta == 0) {
            throw new InvalidArgumentException('El ajuste debe ser una cantidad distinta de cero.');
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Es obligatorio especificar el motivo del ajuste.');
        }

        return DB::transaction(function () use ($product, $location, $quantityDelta, $user, $reason) {
            $stock = $this->getStockLevel($product->id, $location->id, true);

            if ($stock->quantity + $quantityDelta < 0) {
                throw new InvalidArgumentException('El ajuste resultaría en un stock negativo ('.($stock->quantity + $quantityDelta).').');
            }

            $stock->quantity += $quantityDelta;
            $stock->save();

            // If negative adjustment on perishable, deduct batches FEFO
            if ($product->tracks_expiry && $quantityDelta < 0) {
                $this->deductBatchesFefo($product->id, $location->id, abs($quantityDelta));
            }

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'user_id' => $user->id,
                'type' => StockMovement::TYPE_ADJUSTMENT,
                'quantity' => $quantityDelta,
                'reason' => $reason,
            ]);

            AuditLog::record('stock.adjustment', $product, [
                'location' => $location->name,
                'delta' => $quantityDelta,
                'reason' => $reason,
            ]);

            return $movement;
        });
    }

    /**
     * Record physical in-store sale by Cashier (decreases physical store stock).
     */
    public function recordStoreSale(
        Product $product,
        Location $storeLocation,
        float $quantity,
        User $cashier
    ): StockMovement {
        if (! $storeLocation->isStore()) {
            throw new InvalidArgumentException('Las ventas de caja solo pueden registrarse en ubicaciones de tienda física.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('La cantidad vendida debe ser mayor que 0.');
        }

        return DB::transaction(function () use ($product, $storeLocation, $quantity, $cashier) {
            $stock = $this->getStockLevel($product->id, $storeLocation->id, true);

            if ($stock->available < $quantity) {
                throw new InvalidArgumentException('Stock disponible insuficiente en tienda para realizar la venta ('.$stock->available.' disponible).');
            }

            $stock->quantity -= $quantity;
            $stock->save();

            if ($product->tracks_expiry) {
                $this->deductBatchesFefo($product->id, $storeLocation->id, $quantity);
            }

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $storeLocation->id,
                'user_id' => $cashier->id,
                'type' => StockMovement::TYPE_STORE_SALE,
                'quantity' => -$quantity,
                'reason' => 'Venta en caja tienda',
            ]);

            AuditLog::record('stock.store_sale', $product, [
                'location' => $storeLocation->name,
                'quantity' => $quantity,
                'cashier' => $cashier->name,
            ]);

            return $movement;
        });
    }

    /**
     * First-Expired, First-Out (FEFO) batch deduction.
     */
    protected function deductBatchesFefo(int $productId, int $locationId, float $quantity): void
    {
        $remaining = $quantity;

        $batches = Batch::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min((float) $batch->quantity, $remaining);
            $batch->quantity -= $deduct;
            $batch->save();
            $remaining -= $deduct;
        }
    }

    /**
     * Transfer batches between locations maintaining lot codes and expiry dates.
     */
    protected function transferBatchesFefo(int $productId, int $fromLocationId, int $toLocationId, float $quantity): void
    {
        $remaining = $quantity;

        $batches = Batch::where('product_id', $productId)
            ->where('location_id', $fromLocationId)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $transferAmount = min((float) $batch->quantity, $remaining);
            $batch->quantity -= $transferAmount;
            $batch->save();

            // Target batch with same lot and expiry
            $targetBatch = Batch::where('product_id', $productId)
                ->where('location_id', $toLocationId)
                ->where('lot_code', $batch->lot_code)
                ->where('expiry_date', $batch->expiry_date)
                ->first();

            if ($targetBatch) {
                $targetBatch->quantity += $transferAmount;
                $targetBatch->save();
            } else {
                Batch::create([
                    'product_id' => $productId,
                    'location_id' => $toLocationId,
                    'lot_code' => $batch->lot_code,
                    'expiry_date' => $batch->expiry_date,
                    'quantity' => $transferAmount,
                ]);
            }

            $remaining -= $transferAmount;
        }
    }
}
