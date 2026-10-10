<?php

namespace App\Services\Store;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function createOrderFromCart(string $paymentMethod, ?string $transactionId = null, ?float $totalAmount = null): Order
    {
        $cart = session('cart', []);

        if ($cart === []) {
            throw new \RuntimeException('No hay productos en el carrito.');
        }

        if ($totalAmount === null) {
            $totalAmount = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        }

        $shipping = session('checkout.shipping', []);

        $order = Order::create([
            'customer_id' => Auth::guard('customer')->id(),
            'guest_email' => $shipping['email'] ?? null,
            'total_amount' => $totalAmount,
            'status' => 'completed',
        ]);

        foreach ($cart as $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        if ($shipping !== []) {
            ShippingAddress::create([
                'order_id' => $order->id,
                'customer_id' => Auth::guard('customer')->id(),
                'name' => $shipping['name'] ?? null,
                'phone' => $shipping['phone'] ?? null,
                'address' => trim(($shipping['address'] ?? '').' '.($shipping['suite'] ?? '')),
                'city' => $shipping['city'] ?? null,
                'postal_code' => $shipping['postal_code'] ?? null,
                'country' => $shipping['country'] ?? config('store.country_name'),
            ]);
        }

        session()->forget(['cart', 'cart_count', 'checkout.shipping', 'checkout.mercadopago_reference', 'cart_coupon']);

        return $order;
    }

    /**
     * Create a new order from PayPal payment and session cart.
     *
     * @param  array  $paypalResult  The PayPal API capture result
     */
    public function createOrderFromPaypal(array $paypalResult): Order
    {
        // Extract PayPal payer & order data
        $payer = $paypalResult['payer'] ?? [];
        $purchaseUnit = $paypalResult['purchase_units'][0] ?? [];
        $amount = $purchaseUnit['payments']['captures'][0]['amount']['value'] ?? 0;

        // Create Order
        $order = Order::create([
            'customer_id' => Auth::check() ? Auth::id() : null,
            'guest_email' => $payer['email_address'] ?? null,
            'total_amount' => $amount,
            'status' => 'completed',
        ]);

        // Create Order Details from cart session
        $cart = session('cart', []);
        foreach ($cart as $productId => $item) {
            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // Create Shipping Address if available
        $shippingData = session('checkout.shipping');
        if ($shippingData) {
            ShippingAddress::create([
                'order_id' => $order->id,
                'customer_id' => Auth::check() ? Auth::id() : null,
                'name' => $shippingData['name'],
                'phone' => $shippingData['phone'],
                'address' => $shippingData['address'],
                'city' => $shippingData['city'],
                'postal_code' => $shippingData['postal_code'],
                'country' => $shippingData['country'],
            ]);
        }

        // Clear cart session
        session()->forget(['cart', 'checkout.shipping']);

        return $order;
    }
}
