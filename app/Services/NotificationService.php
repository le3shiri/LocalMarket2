<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send order confirmation email to customer.
     */
    public function sendOrderConfirmation(Order $order): void
    {
        $subject = "LocalMarket — Confirmación de tu pedido #{$order->number}";

        $messageBody = "Hola {$order->customer_name},\n\n".
            "¡Gracias por tu compra en LocalMarket!\n\n".
            "Hemos recibido tu pedido correctamente.\n".
            "Número de pedido: {$order->number}\n".
            'Importe total: '.number_format($order->total, 2, ',', '.')." €\n".
            'Método: '.($order->isDelivery() ? 'Entrega a domicilio (Barcelona)' : 'Recogida en tienda')."\n\n".
            ($order->isDelivery() ? "Dirección de entrega: {$order->address}, {$order->postcode} {$order->city}\n\n" : "Punto de recogida: Carrer del Mercat, 12, Ciutat Vella, Barcelona\n\n").
            "Puedes seguir el estado de tu pedido en cualquier momento desde tu cuenta en LocalMarket:\n".
            route('account.orders.show', $order->id)."\n\n".
            "Atentamente,\nEl equipo de LocalMarket Barcelona";

        $this->deliverEmail($order->customer_email, $subject, $messageBody, 'order.confirmation', $order);
    }

    /**
     * Send order status update email to customer.
     */
    public function sendStatusUpdate(Order $order, string $newStatus, ?string $note = null): void
    {
        $statusLabel = $order->statusLabel();
        $subject = "LocalMarket — Actualización de tu pedido #{$order->number}: {$statusLabel}";

        $messageBody = "Hola {$order->customer_name},\n\n".
            "Tu pedido #{$order->number} ha cambiado de estado a: {$statusLabel}.\n\n";

        if ($newStatus === Order::STATUS_READY_FOR_PICKUP) {
            $messageBody .= "¡Tu pedido ya está listo para recoger en tienda! Puedes pasar por nuestro establecimiento en Carrer del Mercat, 12, Barcelona cuando lo desees.\n\n";
        } elseif ($newStatus === Order::STATUS_OUT_FOR_DELIVERY) {
            $messageBody .= "¡Nuestro repartidor va en camino! Tu pedido llegará a tu domicilio en breve.\n\n";
        } elseif ($newStatus === Order::STATUS_COMPLETED) {
            $messageBody .= "Tu pedido ha sido completado y entregado. ¡Esperamos que disfrutes de tus productos frescos!\n\n";
        } elseif ($newStatus === Order::STATUS_CANCELLED) {
            $messageBody .= "Tu pedido ha sido cancelado. Si ya se había realizado el cobro, se procederá al reembolso correspondiente.\n\n";
        }

        if ($note) {
            $messageBody .= "Nota de la tienda: {$note}\n\n";
        }

        $messageBody .= "Ver detalles completos del pedido:\n".
            route('account.orders.show', $order->id)."\n\n".
            "Atentamente,\nEl equipo de LocalMarket Barcelona";

        $this->deliverEmail($order->customer_email, $subject, $messageBody, 'order.status_update', $order);
    }

    /**
     * Deliver the raw plain text email via configured Laravel mail driver
     * (in testing/local, logs to laravel.log or array transport).
     */
    protected function deliverEmail(string $to, string $subject, string $body, string $actionType, Order $order): void
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from(config('mail.from.address', 'pedidos@localmarket.test'), config('mail.from.name', 'LocalMarket Barcelona'));
            });

            AuditLog::record('notification.email_sent', $order, [
                'recipient' => $to,
                'subject' => $subject,
                'action' => $actionType,
            ]);
        } catch (\Throwable $e) {
            Log::error("Error enviando email a {$to} para pedido {$order->number}: ".$e->getMessage());
        }
    }
}
