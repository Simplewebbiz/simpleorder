<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\{Cart, CartItem, CartItemOption, Order, OrderItem, OrderItemOption, OrderItemOptionValue, Setting};
use App\Services\CartService;
use App\Services\GeocodingService;
use App\Services\OrderService;
use App\Jobs\SendOrderConfirmation;
use App\Events\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private GeocodingService $geocodingService,
        private OrderService $orderService,
    ) {}

    public function save(Request $request)
    {
        $cart = $this->cartService->getOrCreate($request);
        $cart->update([
            'method'           => $request->method,
            'delivery_address' => $request->delivery_address['address'] ?? null,
            'delivery_city'    => $request->delivery_address['city'] ?? null,
            'delivery_state'   => $request->delivery_address['state'] ?? null,
            'delivery_zip'     => $request->delivery_address['zip'] ?? null,
            'tip'              => $request->tip ?? null,
        ]);

        return response()->json($this->cartService->format($cart));
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'id'  => 'required|exists:items,id',
            'qty' => 'required|integer|min:1|max:99',
        ]);

        $cart = $this->cartService->getOrCreate($request);
        $cartItem = $this->cartService->addOrUpdateItem($cart, $request->all());

        return response()->json($this->cartService->format($cart));
    }

    public function removeItem(Request $request)
    {
        $cart = $this->cartService->getOrCreate($request);
        $this->cartService->removeItem($cart, $request->cart_id);

        return response()->json($this->cartService->format($cart));
    }

    public function validateAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'city'    => 'required|string',
            'state'   => 'required|string|size:2',
            'zip'     => 'required|string|size:5',
        ]);

        $fullAddress = $request->address . ' ' . $request->city . ', ' . $request->state . ' ' . $request->zip;
        $storeAddress = Setting::get('store_address');
        $radius = (float) Setting::get('delivery_radius', 10);

        $result = $this->geocodingService->validateDeliveryRadius($fullAddress, $storeAddress, $radius);

        if (!$result['valid']) {
            return response()->json([
                'errors' => ['address' => ['Sorry, your address is outside our delivery area (' . $radius . ' mile radius).']],
            ], 422);
        }

        return response()->json(['valid' => true]);
    }

    public function validatePayment(Request $request)
    {
        $request->validate([
            'contact_firstname' => 'required|string|max:100',
            'contact_lastname'  => 'required|string|max:100',
            'contact_email'     => 'required|email',
            'contact_phone'     => 'required|string',
            'billing_firstname' => 'required|string|max:100',
            'billing_lastname'  => 'required|string|max:100',
            'billing_address'   => 'required|string',
            'billing_city'      => 'required|string',
            'billing_state'     => 'required|string|size:2',
            'billing_zip'       => 'required|string|size:5',
        ]);

        $cart = $this->cartService->getOrCreate($request);

        if ($cart->items->isEmpty()) {
            abort(422, 'Your cart is empty.');
        }

        $total = $this->cartService->calculateTotal($cart);

        $intent = $this->orderService->createOrUpdatePaymentIntent($cart, $total);

        $cart->update(['stripe_intent' => $intent->id]);

        return response()->json(['intent' => $intent->client_secret]);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'contact_firstname' => 'required|string',
            'contact_lastname'  => 'required|string',
            'contact_email'     => 'required|email',
            'contact_phone'     => 'required|string',
            'billing_firstname' => 'required|string',
            'billing_lastname'  => 'required|string',
            'billing_address'   => 'required|string',
            'billing_city'      => 'required|string',
            'billing_state'     => 'required|string',
            'billing_zip'       => 'required|string',
        ]);

        $cart = $this->cartService->getOrCreate($request);
        $order = $this->orderService->createFromCart($cart, $request->all());

        // Notify customer + store
        SendOrderConfirmation::dispatch($order, tenant());
        broadcast(new OrderPlaced($order))->toOthers();

        // Reset cart
        $cart->update(['stripe_intent' => null]);
        $cart->items()->delete();

        return response()->json([
            'order' => $this->orderService->formatForResponse($order),
        ]);
    }
}
