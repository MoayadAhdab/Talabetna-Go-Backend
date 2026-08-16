<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateCart(
        Customer $customer,
        Branch $branch
    ): Cart {
        $branch->loadMissing('business');

        $this->validateBranch($branch);

        return Cart::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'branch_id' => $branch->id,
                'status' => 'active',
            ],
            [
                'items_count' => 0,
                'subtotal' => 0,
                'delivery_fee' => 0,
                'discount' => 0,
                'total' => 0,
            ]
        );
    }

    public function getCart(
        Customer $customer,
        Branch $branch
    ): Cart {
        $cart = Cart::query()
            ->where('customer_id', $customer->id)
            ->where('branch_id', $branch->id)
            ->where('status', 'active')
            ->with([
                'customer',
                'branch.business',
                'items.product',
            ])
            ->first();

        if (! $cart) {
            $cart = $this->getOrCreateCart($customer, $branch);
        }

        return $this->recalculate($cart);
    }

    public function addItem(
        Cart $cart,
        Product $product,
        int $quantity = 1,
        array $selectedModifiers = [],
        ?string $notes = null
    ): CartItem {
        return DB::transaction(function () use (
            $cart,
            $product,
            $quantity,
            $selectedModifiers,
            $notes
        ) {
            $cart->loadMissing('branch.business');

            $this->validateCart($cart);

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity must be at least 1.',
                ]);
            }

            $this->validateProduct($cart, $product);

            $modifierData = $this->validateAndCalculateModifiers(
                $product,
                $selectedModifiers
            );

            $unitPrice = $this->getProductPrice($product);

            /*
             * Same product + same modifier selection = same cart item.
             */
            $existingItem = $this->findExistingItem(
                $cart,
                $product,
                $modifierData['snapshot']
            );

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $unitPrice,
                    'modifiers_price' => $modifierData['total'],
                    'subtotal' => (
                        ($unitPrice + $modifierData['total'])
                        * $newQuantity
                    ),
                    'notes' => $notes ?? $existingItem->notes,
                    'selected_modifiers' => $modifierData['snapshot'],
                ]);

                $this->recalculate($cart);

                return $existingItem->fresh([
                    'product',
                    'cart',
                ]);
            }

            $subtotal = (
                ($unitPrice + $modifierData['total'])
                * $quantity
            );

            $item = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'modifiers_price' => $modifierData['total'],
                'subtotal' => $subtotal,
                'selected_modifiers' => $modifierData['snapshot'],
                'notes' => $notes,
            ]);

            $this->recalculate($cart);

            return $item->fresh([
                'product',
                'cart',
            ]);
        });
    }

    public function updateItem(
        CartItem $item,
        int $quantity,
        ?array $selectedModifiers = null,
        ?string $notes = null
    ): CartItem {
        return DB::transaction(function () use (
            $item,
            $quantity,
            $selectedModifiers,
            $notes
        ) {
            $item->loadMissing('cart.branch.business', 'product');

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'quantity' => 'Quantity must be at least 1.',
                ]);
            }

            $cart = $item->cart;
            $product = $item->product;

            $this->validateCart($cart);
            $this->validateProduct($cart, $product);

            $modifierData = $selectedModifiers !== null
                ? $this->validateAndCalculateModifiers(
                    $product,
                    $selectedModifiers
                )
                : [
                    'total' => (float) $item->modifiers_price,
                    'snapshot' => $item->selected_modifiers ?? [],
                ];

            $unitPrice = $this->getProductPrice($product);

            /*
             * If modifiers changed, check if another item already
             * represents the same product + modifiers combination.
             */
            $sameItem = $this->findExistingItem(
                $cart,
                $product,
                $modifierData['snapshot'],
                $item->id
            );

            if ($sameItem) {
                $sameItem->update([
                    'quantity' => $sameItem->quantity + $quantity,
                ]);

                $sameItem->update([
                    'unit_price' => $unitPrice,
                    'modifiers_price' => $modifierData['total'],
                    'subtotal' => (
                        ($unitPrice + $modifierData['total'])
                        * $sameItem->quantity
                    ),
                    'selected_modifiers' => $modifierData['snapshot'],
                    'notes' => $notes ?? $sameItem->notes,
                ]);

                $item->delete();

                $this->recalculate($cart);

                return $sameItem->fresh([
                    'product',
                    'cart',
                ]);
            }

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'modifiers_price' => $modifierData['total'],
                'subtotal' => (
                    ($unitPrice + $modifierData['total'])
                    * $quantity
                ),
                'selected_modifiers' => $modifierData['snapshot'],
                'notes' => $notes ?? $item->notes,
            ]);

            $this->recalculate($cart);

            return $item->fresh([
                'product',
                'cart',
            ]);
        });
    }

    public function removeItem(CartItem $item): void
    {
        DB::transaction(function () use ($item) {
            $item->loadMissing('cart');

            $cart = $item->cart;

            $item->delete();

            $this->recalculate($cart);
        });
    }

    public function clear(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            $cart->items()->delete();

            $this->recalculate($cart);
        });
    }

    public function recalculate(Cart $cart): Cart
    {
        $cart->loadMissing('items', 'branch');

        $subtotal = (float) $cart->items->sum(
            fn (CartItem $item) => (float) $item->subtotal
        );

        $itemsCount = (int) $cart->items->sum(
            fn (CartItem $item) => (int) $item->quantity
        );

        /*
         * No delivery charge when cart is empty.
         */
        $deliveryFee = $subtotal > 0
            ? $this->calculateDeliveryFee($cart)
            : 0;

$discount = 0;

if ($cart->coupon_id) {
    $coupon = $cart->coupon;

    if ($coupon) {
        try {
            app(\App\Services\CouponService::class)
                ->validate($cart, $coupon);

            $discount = app(\App\Services\CouponService::class)
                ->calculateDiscount($cart, $coupon);
        } catch (\Illuminate\Validation\ValidationException) {
            $cart->update([
                'coupon_id' => null,
                'coupon_code' => null,
            ]);

            $discount = 0;
        }
    }
}

$discount = max(
    0,
    min($discount, $subtotal)
);
        $total = max(
            0,
            $subtotal + $deliveryFee - $discount
        );

        $cart->update([
            'items_count' => $itemsCount,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
        ]);

        return $cart->fresh([
            'customer',
            'branch.business',
            'items.product',
        ]);
    }

    /**
     * Validate whether the cart is allowed to proceed to checkout.
     */
    public function validateForCheckout(Cart $cart): Cart
    {
        $cart->loadMissing('branch.business', 'items.product');

        $this->validateCart($cart);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $this->recalculate($cart);
        $cart->refresh();

        $minimumOrder = (float) (
            $cart->branch->minimum_order_amount ?? 0
        );

        if ((float) $cart->subtotal < $minimumOrder) {
            throw ValidationException::withMessages([
                'cart' => sprintf(
                    'Minimum order amount is %.2f.',
                    $minimumOrder
                ),
            ]);
        }

        return $cart;
    }

    protected function findExistingItem(
        Cart $cart,
        Product $product,
        array $selectedModifiers,
        ?int $excludeItemId = null
    ): ?CartItem {
        $query = $cart->items()
            ->where('product_id', $product->id);

        if ($excludeItemId !== null) {
            $query->whereKeyNot($excludeItemId);
        }

        $items = $query->get();

        foreach ($items as $item) {
            if (
                $this->normalizeModifierSnapshot(
                    $item->selected_modifiers ?? []
                )
                ===
                $this->normalizeModifierSnapshot(
                    $selectedModifiers
                )
            ) {
                return $item;
            }
        }

        return null;
    }

    protected function normalizeModifierSnapshot(
        array $snapshot
    ): array {
        $normalized = collect($snapshot)
            ->map(function (array $group) {
                $options = collect($group['options'] ?? [])
                    ->map(function (array $option) {
                        return [
                            'id' => (int) ($option['id'] ?? 0),
                            'name' => trim((string) ($option['name'] ?? '')),
                            'price' => round(
                                (float) ($option['price'] ?? 0),
                                2
                            ),
                        ];
                    })
                    ->sortBy('id')
                    ->values()
                    ->all();

                return [
                    'group_id' => (int) ($group['group_id'] ?? 0),
                    'group_name' => trim(
                        (string) ($group['group_name'] ?? '')
                    ),
                    'options' => $options,
                ];
            })
            ->sortBy('group_id')
            ->values()
            ->all();

        return $normalized;
    }

    protected function validateBranch(Branch $branch): void
    {
        if (! $branch->is_active) {
            throw ValidationException::withMessages([
                'branch' => 'This branch is currently inactive.',
            ]);
        }

        if (! $branch->is_accepting_orders) {
            throw ValidationException::withMessages([
                'branch' => 'This branch is not currently accepting orders.',
            ]);
        }

        if (! $branch->business) {
            throw ValidationException::withMessages([
                'branch' => 'This branch has no associated business.',
            ]);
        }

        if (! $branch->business->is_active) {
            throw ValidationException::withMessages([
                'branch' => 'This business is currently inactive.',
            ]);
        }
    }

    protected function validateCart(Cart $cart): void
    {
        if ($cart->status !== 'active') {
            throw ValidationException::withMessages([
                'cart' => 'This cart is no longer active.',
            ]);
        }

        $cart->loadMissing('branch.business');

        $this->validateBranch($cart->branch);
    }

    protected function validateProduct(
        Cart $cart,
        Product $product
    ): void {
        $product->loadMissing([
            'business',
            'category',
        ]);

        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'product' => 'This product is inactive.',
            ]);
        }

        if (! $product->is_available) {
            throw ValidationException::withMessages([
                'product' => 'This product is currently unavailable.',
            ]);
        }

        if (! $product->business) {
            throw ValidationException::withMessages([
                'product' => 'This product has no associated business.',
            ]);
        }

        if ($product->business_id !== $cart->branch->business_id) {
            throw ValidationException::withMessages([
                'product' => 'This product does not belong to this branch business.',
            ]);
        }

        if (
            $product->category &&
            $product->category->business_id !== $cart->branch->business_id
        ) {
            throw ValidationException::withMessages([
                'product' => 'This product category does not belong to this business.',
            ]);
        }
    }

    protected function getProductPrice(Product $product): float
    {
        if (
            $product->sale_price !== null &&
            (float) $product->sale_price < (float) $product->price
        ) {
            return (float) $product->sale_price;
        }

        return (float) $product->price;
    }

    protected function validateAndCalculateModifiers(
        Product $product,
        array $selectedModifiers
    ): array {
        $product->loadMissing([
            'modifierGroups.options',
        ]);

        $groups = $product->modifierGroups
            ->where('is_active', true)
            ->keyBy('id');

        /*
         * If the product has no modifier groups, no modifiers
         * should be accepted.
         */
        if ($groups->isEmpty() && ! empty($selectedModifiers)) {
            throw ValidationException::withMessages([
                'modifiers' => 'This product does not support modifiers.',
            ]);
        }

        foreach (array_keys($selectedModifiers) as $groupId) {
            if (! $groups->has((int) $groupId)) {
                throw ValidationException::withMessages([
                    'modifiers' => 'One or more selected modifier groups are invalid.',
                ]);
            }
        }

        $snapshot = [];
        $total = 0;

        foreach ($groups as $group) {
            $selectedOptionIds = collect(
                $selectedModifiers[$group->id] ?? []
            )
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $count = $selectedOptionIds->count();

            if ($count < $group->min_selections) {
                throw ValidationException::withMessages([
                    'modifiers' => "Modifier group '{$group->name}' requires at least {$group->min_selections} selection(s).",
                ]);
            }

            if ($count > $group->max_selections) {
                throw ValidationException::withMessages([
                    'modifiers' => "Modifier group '{$group->name}' allows a maximum of {$group->max_selections} selection(s).",
                ]);
            }

            if ($count === 0) {
                continue;
            }

            $allowedOptions = $group->options
                ->where('is_active', true)
                ->keyBy('id');

            $groupSnapshot = [];

            foreach ($selectedOptionIds as $optionId) {
                $option = $allowedOptions->get($optionId);

                if (! $option) {
                    throw ValidationException::withMessages([
                        'modifiers' => "Invalid option selected for '{$group->name}'.",
                    ]);
                }

                $optionPrice = (float) $option->price;

                $total += $optionPrice;

                $groupSnapshot[] = [
                    'id' => $option->id,
                    'name' => trim($option->name),
                    'price' => round($optionPrice, 2),
                ];
            }

            $snapshot[] = [
                'group_id' => $group->id,
                'group_name' => trim($group->name),
                'options' => $groupSnapshot,
            ];
        }

        return [
            'total' => round($total, 2),
            'snapshot' => $snapshot,
        ];
    }

    protected function calculateDeliveryFee(Cart $cart): float
    {
        return (float) ($cart->branch->delivery_fee ?? 0);
    }
}