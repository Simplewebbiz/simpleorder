<?php

namespace App\Services;

use App\Models\Tenant\{Cart, Coupon, Order, OrderItem, OrderItemOption, OrderItemOptionValue, Setting};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class OrderService
{
    private StripeClient $stripe;
    private string $mode; // connect | direct | platform

    public function __construct()
    {
        $connectId = Setting::get('stripe_connect_id');
        $directKey = Setting::get('stripe_secret_key');

        if ($directKey) {
            $this->stripe = new StripeClient($directKey);
            $this->mode = 'direct';
        } elseif ($connectId) {
            $this->stripe = new StripeClient(config('stripe.secret'));
            $this->mode = 'connect';
        } else {
            $this->stripe = new StripeClient(config('stripe.secret'));
            $this->mode = 'platform';
        }
    }

    public function createOrUpdatePaymentIntent(Cart $cart, array $totals): \Stripe\PaymentIntent
    {
        $amountCents = (int) round($totals['total'] * 100);

        $params = [
            'amount' => $amountCents,
            'currency' => 'usd',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => ['cart_id' => $cart->id, 'tenant' => tenant()->id],
        ];

        if ($this->mode === 'connect') {
            $connectId = Setting::get('stripe_connect_id');
            $platformFee = (int) round($amountCents * config('stripe.platform_fee', 0.02));
            $params['application_fee_amount'] = $platformFee;
            $params['transfer_data'] = ['destination' => $connectId];
        }

        if ($cart->stripe_intent) {
            try {
                return $this->stripe->paymentIntents->update($cart->stripe_intent, ['amount' => $amountCents]);
            } catch (\Exception $e) {
                // Create a fresh intent when the saved intent is missing or expired.
            }
        }

        return $this->stripe->paymentIntents->create($params);
    }

    public function createFromCart(Cart $cart, array $data): Order
    {
        $cart->loadMissing([
            'items.item',
            'items.options.option',
            'items.options.values.optionValue',
        ]);

        $totals = app(CartService::class)->calculateTotal($cart);

        $intent = $this->stripe->paymentIntents->retrieve($cart->stripe_intent);
        $charge = null;

        if (! empty($intent->latest_charge)) {
            $charge = $this->stripe->charges->retrieve($intent->latest_charge);
        }

        return DB::transaction(function () use ($cart, $data, $totals, $charge) {
            $incrementId = DB::table('order_sequences')->insertGetId(['next_id' => 1]);

            $order = Order::create([
                'increment_id' => $incrementId,
                'key' => Str::random(32),
                'user_id' => auth('tenant')->id(),
                'cart_id' => $cart->id,
                'method' => $cart->method,
                'status' => 'placed',
                'contact_firstname' => $data['contact_firstname'],
                'contact_lastname' => $data['contact_lastname'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'],
                'delivery_address' => $cart->delivery_address,
                'delivery_city' => $cart->delivery_city,
                'delivery_state' => $cart->delivery_state,
                'delivery_zip' => $cart->delivery_zip,
                'billing_firstname' => $data['billing_firstname'],
                'billing_lastname' => $data['billing_lastname'],
                'billing_address' => $data['billing_address'],
                'billing_city' => $data['billing_city'],
                'billing_state' => $data['billing_state'],
                'billing_zip' => $data['billing_zip'],
                'stripe_payment_intent' => $cart->stripe_intent,
                'stripe_charge_id' => $charge->id ?? null,
                'card_brand' => $charge->payment_method_details->card->brand ?? null,
                'card_last4' => $charge->payment_method_details->card->last4 ?? null,
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'delivery' => $totals['delivery'],
                'tip' => $totals['tip'],
                'coupon_code' => $totals['coupon_code'],
                'discount' => $totals['discount'],
                'total' => $totals['total'],
                'comments' => $data['notes'] ?? null,
            ]);

            if (! empty($totals['coupon_code'])) {
                Coupon::where('code', $totals['coupon_code'])->increment('used_count');
            }

            foreach ($cart->items as $cartItem) {
                $item = $cartItem->item;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'taxable' => $item->taxable,
                    'type' => $item->type,
                    'qty' => $cartItem->qty,
                    'price' => $item->price,
                    'comments' => $cartItem->comments,
                ]);

                foreach ($cartItem->options as $cartOption) {
                    $orderOption = OrderItemOption::create([
                        'order_item_id' => $orderItem->id,
                        'label' => $cartOption->option->label,
                    ]);

                    foreach ($cartOption->values as $cartValue) {
                        OrderItemOptionValue::create([
                            'order_item_option_id' => $orderOption->id,
                            'label' => $cartValue->optionValue->label,
                            'price' => $cartValue->optionValue->price,
                            'price_type' => $cartValue->optionValue->price_type,
                        ]);
                    }
                }
            }

            return $order->load('items.options.values');
        });
    }

    public function formatForResponse(Order $order): array
    {
        return [
            'id' => $order->id,
            'key' => $order->key,
            'increment_id' => $order->increment_id,
            'status' => $order->status,
            'method' => $order->method,
            'contact_firstname' => $order->contact_firstname,
            'contact_lastname' => $order->contact_lastname,
            'contact_email' => $order->contact_email,
            'contact_phone' => $order->contact_phone,
            'delivery_address' => $order->delivery_address,
            'delivery_city' => $order->delivery_city,
            'delivery_state' => $order->delivery_state,
            'delivery_zip' => $order->delivery_zip,
            'billing_firstname' => $order->billing_firstname,
            'billing_lastname' => $order->billing_lastname,
            'billing_address' => $order->billing_address,
            'billing_city' => $order->billing_city,
            'billing_state' => $order->billing_state,
            'billing_zip' => $order->billing_zip,
            'card_brand' => $order->card_brand,
            'card_last4' => $order->card_last4,
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'delivery' => (float) $order->delivery,
            'tip' => (float) $order->tip,
            'coupon_code' => $order->coupon_code,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'comments' => $order->comments,
            'created_at' => $order->created_at->toISOString(),
            'items' => $order->items->map(fn ($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'qty' => $i->qty,
                'price' => (float) $i->price,
                'taxable' => $i->taxable,
                'type' => $i->type,
                'comments' => $i->comments,
                'options' => $i->options->map(fn ($o) => [
                    'label' => $o->label,
                    'values' => $o->values->map(fn ($v) => [
                        'label' => $v->label,
                        'price' => (float) $v->price,
                        'price_type' => $v->price_type,
                    ]),
                ]),
            ]),
        ];
    }
}