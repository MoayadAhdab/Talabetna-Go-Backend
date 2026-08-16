<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    protected array $allowedTransitions = [
        'pending' => [
            'confirmed',
            'cancelled',
            'rejected',
        ],

        'confirmed' => [
            'preparing',
            'cancelled',
        ],

        'preparing' => [
            'ready',
            'cancelled',
        ],

        'ready' => [
            'out_for_delivery',
        ],

        'out_for_delivery' => [
            'delivered',
            'cancelled',
        ],

        'delivered' => [],

        'cancelled' => [],

        'rejected' => [],
    ];

    public function changeStatus(
        Order $order,
        string $newStatus,
        ?string $note = null,
        ?int $userId = null
    ): Order {
        return DB::transaction(function () use (
            $order,
            $newStatus,
            $note,
            $userId
        ) {
            $oldStatus = $order->status;

            if ($oldStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' => 'The order is already in this status.',
                ]);
            }

            if (! isset($this->allowedTransitions[$oldStatus])) {
                throw ValidationException::withMessages([
                    'status' => 'Invalid current order status.',
                ]);
            }

            if (
                ! in_array(
                    $newStatus,
                    $this->allowedTransitions[$oldStatus],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'status' => "Order cannot move from {$oldStatus} to {$newStatus}.",
                ]);
            }

            $order->update([
                'status' => $newStatus,
            ]);

            $order->statusHistory()->create([
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => $userId ?? auth()->id(),
                'note' => $note,
            ]);

            return $order->fresh([
                'statusHistory.changedBy',
            ]);
        });
    }
}