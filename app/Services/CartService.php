<?php

namespace App\Services;

use App\Models\Tenant\Cart;
use App\Models\Tenant\CartItem;
use App\Models\Tenant\CartItemOption;
use App\Models\Tenant\CartItemOptionValue;
use App\Models\Tenant\Coupon;
use App\Models\Tenant\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartService
{
    public function getOrCreate(Request $request): Cart
    {
        $key = $request->session()->get('cart_key');

        if ($key) {
            $cart = Cart::where('session_key', $key)->whereNull('deleted_at')->first();
            if ($cart) {
                return $cart;
            }
        }

        $key = Str::uuid()->toString();
        $request->session()->put('cart_key', $key);

        return Cart::create([
            'session_key' => $key,
            'user_id' => auth('tenant')->id(),
        ]);
    }

    public function addOrUpdateItem(Cart $cart, array $data): CartItem
    {
        if (! empty($data['cart_id'])) {
            $cartItem = CartItem::where('id', $data['cart_id'])->where('cart_id', $cart->id)->firstOrFail();
            $cartItem->update(['qty' => $data['qty'], 'comments' => $data['comments'] ?? null]);
            $cartItem->options()->delete();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'item_id' => $data['id'],
                'qty' => $data['qty'],
                'comments' => $data['comments'] ?? null,
            ]);
        }

        foreach ($data['selections'] ?? [] as $selection) {
            $cartOption = CartItemOption::create([
                'cart_item_id' => $cartItem->id,
                'item_option_id' => $selection['id'],
            ]);

            foreach ($selection['selections'] ?? [] as $value) {
                CartItemOptionValue::create([
                    'cart_item_option_id' => $cartOption->id,
                    'item_option_value_id' => $value['id'],
                ]);
            }
        }

        return $cartItem;
    }

    public function removeItem(Cart $cart, int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)->where('cart_id', $cart->id)->delete();
    }

    public function calculateTotal(Cart $cart): array
    {
        $cart->load(['items.item', 'items.options.values.optionValue']);

        $subtotal = 0.0;
        $taxable = 0.0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->item;
            $price = (float) $item->price;
            $percent = 1.0;

            foreach ($cartItem->options as $option) {
                foreach ($option->values as $val) {
                    $optionValue = $val->optionValue;
                    if ($optionValue->price_type === 'percent') {
                        $percent += (float) $optionValue->price / 100;
                    } else {
                        $price += (float) $optionValue->price;
                    }
                }
            }

            $lineTotal = round($price * $percent * $cartItem->qty, 2);
            $subtotal += $lineTotal;

            if ($item->taxable) {
                $taxable += $lineTotal;
            }
        }

        $delivery = 0.0;
        if ($cart->method === 'delivery') {
            $foodCharge = (float) Setting::get('food_charge', 0);
            $groceryCharge = (float) Setting::get('grocery_charge', 0);
            $hasFood = $cart->items->contains(fn ($cartItem) => $cartItem->item->type === 'food');
            $delivery = $hasFood ? $foodCharge : $groceryCharge;
        }

        $coupon = $this->couponForCart($cart, $subtotal);
        $discount = $coupon?->discountFor($subtotal, $delivery) ?? 0.0;
        $taxableDiscount = $coupon?->type === 'free_delivery' ? 0.0 : min($discount, $taxable);

        $taxRate = (float) Setting::get('tax_rate', 0) / 100;
        $tax = round(max($taxable - $taxableDiscount, 0) * $taxRate, 2);

        $tip = (float) ($cart->tip ?? 0);
        $total = round(max($subtotal + $tax + $delivery + $tip - $discount, 0), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'delivery' => round($delivery, 2),
            'tip' => round($tip, 2),
            'discount' => round($discount, 2),
            'coupon_code' => $coupon?->code,
            'coupon' => $coupon ? [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
            ] : null,
            'total' => $total,
        ];
    }

    public function format(Cart $cart): array
    {
        $cart->load(['items.item.image', 'items.options.option', 'items.options.values.optionValue']);

        return [
            'id' => $cart->id,
            'method' => $cart->method,
            'delivery_address' => [
                'address' => $cart->delivery_address,
                'city' => $cart->delivery_city,
                'state' => $cart->delivery_state,
                'zip' => $cart->delivery_zip,
            ],
            'tip' => $cart->tip,
            'coupon_code' => $cart->coupon_code,
            'totals' => $this->calculateTotal($cart),
            'items' => $cart->items->map(fn ($cartItem) => [
                'cart_id' => $cartItem->id,
                'id' => $cartItem->item_id,
                'name' => $cartItem->item->name,
                'price' => (float) $cartItem->item->price,
                'taxable' => $cartItem->item->taxable,
                'type' => $cartItem->item->type,
                'qty' => $cartItem->qty,
                'comments' => $cartItem->comments,
                'image' => $cartItem->item->image?->permalink,
                'selections' => $cartItem->options->map(fn ($option) => [
                    'id' => $option->item_option_id,
                    'label' => $option->option->label,
                    'selections' => $option->values->map(fn ($value) => [
                        'id' => $value->item_option_value_id,
                        'label' => $value->optionValue->label,
                        'price' => (float) $value->optionValue->price,
                        'price_type' => $value->optionValue->price_type,
                    ]),
                ]),
            ])->values(),
        ];
    }

    private function couponForCart(Cart $cart, float $subtotal): ?Coupon
    {
        $code = Coupon::normalizeCode($cart->coupon_code);
        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon || ! $coupon->isUsableFor($subtotal)) {
            return null;
        }

        return $coupon;
    }
}