<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

class AccountOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['items.product', 'statusHistories.user'])
            ->firstOrFail();

        return view('account.orders.show', compact('order'));
    }

    public function cancel(Request $request, int $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (! $order->isPendingPayment()) {
            return back()->with('error', 'Solo puedes cancelar pedidos que aún no hayan sido pagados.');
        }

        try {
            $this->orderService->changeStatus(
                $order,
                Order::STATUS_CANCELLED,
                auth()->user(),
                'Cancelado por el cliente desde su panel.'
            );

            return back()->with('success', 'Pedido cancelado y reserva de stock liberada.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
