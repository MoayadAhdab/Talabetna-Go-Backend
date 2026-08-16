<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function createPendingPayment(
        Order $order,
        string $method,
        ?float $amount = null,
        string $currency = 'USD'
    ): Payment {
        if (! in_array($method, [
            'cash',
            'card',
            'online',
            'wallet',
        ], true)) {
            throw ValidationException::withMessages([
                'method' => 'Invalid payment method.',
            ]);
        }

        return $order->payments()->create([
            'method' => $method,
            'status' => 'pending',
            'amount' => $amount ?? (float) $order->total,
            'currency' => $currency,
        ]);
    }

    public function markAsPaid(
        Payment $payment,
        ?string $transactionId = null,
        ?string $reference = null,
        ?array $gatewayResponse = null
    ): Payment {
        return DB::transaction(function () use (
            $payment,
            $transactionId,
            $reference,
            $gatewayResponse
        ) {
            $payment->update([
                'status' => 'paid',
                'transaction_id' => $transactionId,
                'reference' => $reference,
                'paid_at' => now(),
                'gateway_response' => $gatewayResponse,
            ]);

            $order = $payment->order;

            $order->update([
                'payment_status' => 'paid',
            ]);

            return $payment->fresh('order');
        });
    }

    public function markAsFailed(
        Payment $payment,
        ?string $reason = null,
        ?array $gatewayResponse = null
    ): Payment {
        return DB::transaction(function () use (
            $payment,
            $reason,
            $gatewayResponse
        ) {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $reason,
                'gateway_response' => $gatewayResponse,
            ]);

            $payment->order->update([
                'payment_status' => 'failed',
            ]);

            return $payment->fresh('order');
        });
    }

    public function refund(
        Payment $payment,
        ?string $reference = null
    ): Payment {
        return DB::transaction(function () use (
            $payment,
            $reference
        ) {
            if ($payment->status !== 'paid') {
                throw ValidationException::withMessages([
                    'payment' => 'Only paid payments can be refunded.',
                ]);
            }

            $payment->update([
                'status' => 'refunded',
                'reference' => $reference ?? $payment->reference,
            ]);

            $payment->order->update([
                'payment_status' => 'refunded',
            ]);

            return $payment->fresh('order');
        });
    }
}