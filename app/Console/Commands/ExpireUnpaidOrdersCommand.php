<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'orders:expire-unpaid {--minutes= : Override timeout minutes}';

    /**
     * The console command description.
     */
    protected $description = 'Expire pending unpaid orders and release their reserved stock back to available inventory.';

    public function handle(InventoryService $inventoryService, NotificationService $notificationService): int
    {
        $timeoutMinutes = (int) ($this->option('minutes') ?: config('localmarket.payment_timeout_minutes', 30));
        $cutoff = now()->subMinutes($timeoutMinutes);

        $this->info("Buscando pedidos pendientes de pago anteriores a: {$cutoff->toDateTimeString()} ({$timeoutMinutes} min)...");

        $expiredOrders = Order::where('status', Order::STATUS_PENDING_PAYMENT)
            ->where('created_at', '<=', $cutoff)
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No se encontraron pedidos caducados.');

            return self::SUCCESS;
        }

        $onlineLocation = Location::where('is_online_source', true)->first();
        $count = 0;

        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order, $inventoryService, $notificationService, $onlineLocation, &$count, $timeoutMinutes) {
                // Lock order
                $order = Order::where('id', $order->id)->lockForUpdate()->first();

                if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
                    return;
                }

                $order->status = Order::STATUS_CANCELLED;
                $order->cancelled_at = now();
                $order->save();

                // Release reserved stock back to inventory
                if ($onlineLocation) {
                    $inventoryService->releaseReservation($order, $onlineLocation);
                }

                $order->statusHistories()->create([
                    'from_status' => Order::STATUS_PENDING_PAYMENT,
                    'to_status' => Order::STATUS_CANCELLED,
                    'user_id' => null,
                    'note' => "Pedido cancelado automáticamente por expiración del plazo de pago ({$timeoutMinutes} minutos). Reserva de existencias liberada.",
                ]);

                AuditLog::record('order.expired_unpaid', $order, [
                    'order_number' => $order->number,
                    'timeout_minutes' => $timeoutMinutes,
                ]);

                $notificationService->sendStatusUpdate(
                    $order,
                    Order::STATUS_CANCELLED,
                    "Tu pedido ha expirado al superarse el tiempo límite de pago ({$timeoutMinutes} minutos). La reserva de tus artículos ha sido liberada."
                );

                $count++;
            });
        }

        $this->info("Se han cancelado {$count} pedidos expirados y se ha liberado el stock reservado.");

        return self::SUCCESS;
    }
}
