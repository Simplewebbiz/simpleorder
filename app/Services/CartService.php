<?php

namespace App\Services;

use App\Models\Tenant\{Cart, CartItem, CartItemOption, CartItemOptionValue, Item, Setting};
use Illuminate\Http\Request;

class CartService
{
    public function getOrCreate(Request $request): Cart
    {
        $key = $request->session()->get('cart_key');

        if ($key) {
            $cart = Cart::where('session_key', $key)->whereNull('deleted_at')->first();
            if ($cart) return $cart;
        }

        $key = \Str::uuid()->toString();
        $request->session()->put('cart_key', $key);

        return Cart::create([
            'session_key' => $key,
            'user_id'     => auth('tenant')->id(),
        ]);
    }

    public function addOrUpdateItem(Cart $cart, array $data): CartItem
    {
        // If cart_id is set, this is an update
        if (!empty($data['cart_id'])) {
            $cartItem = CartItem::where('id', $data['cart_id'])->where('cart_id', $cart->id)->firstOrFail();
            $cartItem->update(['qty' => $data['qty'], 'comments' => $data['comments'] ?? null]);
            $cartItem->options()->delete();
        } else {
            $cartItem = CartItem::create([
                'cart_id'  => $cart->id,
                'item_id'  => $data['id'],
                'qty'      => $data['qty'],
                'comments' => $data['comments'] ?? null,
            ]);
        }

        // Save selections
        if (!empty($data['selections'])) {
            foreach ($data['selections'] as $selection) {
                $cartOption = CartItemOption::create([
                    'cart_item_id'   => $cartItem->id,
                    'item_option_id' => $selection['id'],
                ]);

                foreach ($selection['selections'] ?? [] as $value) {
                    CartItemOptionValue::create([
                        'cart_item_option_id'   => $cartOption->id,
                        'item_option_value_id'  => $value['id'],
                    ]);
                }
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

        $subtotal = 0;
        $taxable  = 0;

        foreach ($cart->items as $cartItem) {
            $item  = $cartItem->item;
            $price = (float) $item->price;
            $percent = 1.0;

            foreach ($cartItem->options as $option) {
                foreach ($option->values as $val) {
                    $ov = $val->optionValue;
                    if ($ov->price_type === 'percent') {
                        $percent += (float) $ov->price / 100;
                    } else {
                        $price += (float) $ov->price;
                    }
                }
            }

            $lineTotal = round($price * $percent * $cartItem->qty, 2);
            $subtotal += $lineTotal;

            if ($item->taxable) {
                $taxable += $lineTotal;
            }
        }

        $taxRate  = (float) Setting::get('tax_rate', 0) / 100;
        $tax      = round($taxable * $taxRate, 2);

        $delivery = 0;
        if ($cart->method === 'delivery') {
            $foodCharge    = (float) Setting::get('food_charge', 0);
            $groceryCharge = (float) Setting::get('grocery_charge', 0);
            $hasFood       = $cart->items->contains(fn($i) => $i->item->type === 'food');
            $delivery      = $hasFood ? $foodCharge : $groceryCharge;
        }

        $tip   = (float) ($cart->tip ?? 0);
        $total = round($subtotal + $tax + $delivery + $tip, 2);

        return compact('subtotal', 'tax', 'delivery', 'tip', 'total');
    }

    public function format(Cart $cart): array
    {
        $cart->load(['items.item.image', 'items.options.option', 'items.options.values.optionValue']);

        return [
            'id'               => $cart->id,
            'method'           => $cart->method,
            'delivery_address' => [
                'address' => $cart->delivery_address,
                'city'    => $cart->delivery_city,
                'state'   => $cart->delivery_state,
                'zip'     => $cart->delivery_zip,
            ],
            'tip'    => $cart->tip,
            'items'  => $cart->items->map(fn($ci) => [
                'cart_id'    => $ci->id,
                'id'         => $ci->item_id,
                'name'       => $ci->item->name,
                'price'      => (float) $ci->item->price,
                'taxable'    => $ci->item->taxable,
                'type'       => $ci->item->type,
                'qty'        => $ci->qty,
                'comments'   => $ci->comments,
                'image'      => $ci->item->image?->permalink,
                'selections' => $ci->options->map(fn($o) => [
                    'id'         => $o->item_option_id,
                    'label'      => $o->option->label,
                    'selections' => $o->values->map(fn($v) => [
                        'id'         => $v->item_option_value_id,
                        'label'      => $v->optionValue->label,
                        'price'      => (float) $v->optionValue->price,
                        'price_type' => $v->optionValue->price_type,
                    ]),
                ]),
            ])->values(),
        ];
    }
}
