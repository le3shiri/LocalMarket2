<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $query = Order::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fulfilment_method')) {
            $query->where('fulfilment_method', $request->fulfilment_method);
        }

        if ($request->filled('q')) {
            $search = '%'.$request->q.'%';
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', $search)
                    ->orWhere('customer_name', 'like', $search)
                    ->orWhere('customer_email', 'like', $search);
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('staff.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'statusHistories.user']);
        $allowedTransitions = Order::TRANSITIONS[$order->status] ?? [];

        return view('staff.orders.show', compact('order', 'allowedTransitions'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();

        // Only admin can cancel a paid order
        if ($data['status'] === Order::STATUS_CANCELLED && $order->isPaid() && ! $user->hasRole('admin')) {
            return back()->with('error', 'Solo los administradores pueden cancelar un pedido que ya ha sido pagado.');
        }

        try {
            $this->orderService->changeStatus($order, $data['status'], $user, $data['note'] ?? null);

            return back()->with('success', 'Estado del pedido actualizado a: '.$order->statusLabel());
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
