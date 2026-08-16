<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryService
{
    public function assignDriver(
        Order $order,
        Driver $driver
    ): Delivery {
        return DB::transaction(function () use ($order, $driver) {
            if (! $driver->is_active) {
                throw ValidationException::withMessages([
                    'driver' => 'Driver is inactive.',
                ]);
            }

            if (! $driver->is_verified) {
                throw ValidationException::withMessages([
                    'driver' => 'Driver is not verified.',
                ]);
            }

            if (! in_array($driver->status, [
                'available',
                'offline',
            ], true)) {
                throw ValidationException::withMessages([
                    'driver' => 'Driver is currently unavailable.',
                ]);
            }

            $delivery = $order->delivery;

            if (! $delivery) {
                $delivery = $order->delivery()->create([
                    'status' => 'pending',
                    'delivery_fee' => $order->delivery_fee,
                    'delivery_latitude' => data_get(
                        $order->delivery_address,
                        'latitude'
                    ),
                    'delivery_longitude' => data_get(
                        $order->delivery_address,
                        'longitude'
                    ),
                ]);
            }

            $delivery->update([
                'driver_id' => $driver->id,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            $driver->update([
                'status' => 'busy',
            ]);

            $order->update([
                'delivery_status' => 'assigned',
            ]);

            return $delivery->fresh([
                'order',
                'driver',
            ]);
        });
    }

    public function markPickedUp(Delivery $delivery): Delivery
    {
        return DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);

            $delivery->order()->update([
                'delivery_status' => 'picked_up',
            ]);

            return $delivery->fresh();
        });
    }

    public function markOutForDelivery(Delivery $delivery): Delivery
    {
        return DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'out_for_delivery',
                'out_for_delivery_at' => now(),
            ]);

            $delivery->order()->update([
                'delivery_status' => 'out_for_delivery',
            ]);

            return $delivery->fresh();
        });
    }

    public function markDelivered(Delivery $delivery): Delivery
    {
        return DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            $delivery->order()->update([
                'delivery_status' => 'delivered',
            ]);

            if ($delivery->driver) {
                $delivery->driver->update([
                    'status' => 'available',
                ]);
            }

            return $delivery->fresh();
        });
    }

    public function markFailed(
        Delivery $delivery,
        string $reason
    ): Delivery {
        return DB::transaction(function () use ($delivery, $reason) {
            $delivery->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $reason,
            ]);

            $delivery->order()->update([
                'delivery_status' => 'failed',
            ]);

            if ($delivery->driver) {
                $delivery->driver->update([
                    'status' => 'available',
                ]);
            }

            return $delivery->fresh();
        });
    }
}