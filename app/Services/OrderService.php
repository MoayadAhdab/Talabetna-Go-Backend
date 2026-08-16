<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected CartService $cartService
    ) {
    }

    /**
     * Convert an active cart into an order.
     */
    public function checkout(
        Cart $cart,
        Address $address,
        string $paymentMethod = 'cash',
        ?string $customerNote = null
    ): Order {
        return DB::transaction(function () use (
            $cart,
            $address,
            $paymentMethod,
            $customerNote
        ) {
            /*
             * 1. Validate and recalculate the cart.
             */
            $cart = $this->cartService->validateForCheckout($cart);

            /*
             * 2. Validate address.
             */
            $this->validateAddress($cart, $address);

            /*
             * 3. Prevent duplicate checkout.
             */
            if ($cart->status !== 'active') {
                throw ValidationException::withMessages([
                    'cart' => 'This cart has already been converted.',
                ]);
            }

            /*
             * 4. Refresh all relevant relations.
             */
            $cart->load([
                'customer',
                'branch.business',
                'items.product',
            ]);

            /*
             * 5. Create order.
             */
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),

                'customer_id' => $cart->customer_id,
                'branch_id' => $cart->branch_id,
                'cart_id' => $cart->id,

                'status' => 'pending',
                'payment_status' => 'pending',
                'delivery_status' => 'pending',

                'payment_method' => $paymentMethod,

                'customer_note' => $customerNote,

                'subtotal' => $cart->subtotal,
                'delivery_fee' => $cart->delivery_fee,
                'discount' => $cart->discount,
                'tax' => 0,
                'total' => $cart->total,
                'coupon_id' => $cart->coupon_id,
'coupon_code' => $cart->coupon_code,

                'delivery_address' => $this->addressSnapshot($address),

                'branch_snapshot' => [
                    'id' => $cart->branch->id,
                    'name' => $cart->branch->name,
                    'phone' => $cart->branch->phone,
                    'address' => $cart->branch->address,
                    'city' => $cart->branch->city,
                    'area' => $cart->branch->area,
                    'latitude' => $cart->branch->latitude,
                    'longitude' => $cart->branch->longitude,
                    'business_id' => $cart->branch->business_id,
                    'business_name' => $cart->branch->business?->name,
                ],
            ]);
            $order->statusHistory()->create([
    'from_status' => null,
    'to_status' => 'pending',
    'changed_by' => auth()->id(),
    'note' => 'Order created.',
]);
$order->payments()->create([
    'method' => $paymentMethod,
    'status' => 'pending',
    'amount' => $order->total,
    'currency' => 'USD',
]);

            /*
             * 6. Create order item snapshots.
             */
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,

                    'product_name' => $cartItem->product->name,

                    'product_sku' => $cartItem->product->sku,

                    'unit_price' => $cartItem->unit_price,

                    'modifiers_price' => $cartItem->modifiers_price,

                    'quantity' => $cartItem->quantity,

                    'subtotal' => $cartItem->subtotal,

                    'selected_modifiers' => $cartItem->selected_modifiers,

                    'notes' => $cartItem->notes,
                ]);
            }

            /*
             * 7. Convert the cart.
             */
            $cart->update([
                'status' => 'converted',
            ]);
$customer = $cart->customer;

$customer->increment('total_orders');

$customer->increment(
    'total_spent',
    (float) $order->total
);
            /*
             * 8. Return complete order.
             */
            return $order->fresh([
                'customer',
                'branch',
                'items.product',
            ]);
        });
    }

    protected function validateAddress(
        Cart $cart,
        Address $address
    ): void {
        if (! $address->is_active) {
            throw ValidationException::withMessages([
                'address' => 'This address is inactive.',
            ]);
        }

        if ($address->customer_id !== $cart->customer_id) {
            throw ValidationException::withMessages([
                'address' => 'This address does not belong to the customer.',
            ]);
        }
    }

    protected function addressSnapshot(Address $address): array
    {
        return [
            'id' => $address->id,
            'label' => $address->label,

            'contact_name' => $address->contact_name,
            'contact_phone' => $address->contact_phone,

            'address_line' => $address->address_line,
            'building' => $address->building,
            'floor' => $address->floor,
            'apartment' => $address->apartment,

            'city' => $address->city,
            'area' => $address->area,

            'delivery_instructions' => $address->delivery_instructions,

            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
        ];
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'TG-'
                . now()->format('Ymd')
                . '-'
                . str_pad(
                    (string) random_int(1, 999999),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
        } while (
            Order::where('order_number', $number)->exists()
        );

        return $number;
    }
}