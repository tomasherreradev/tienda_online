<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\ProductVariant;
use App\Services\PaymentGateway\MercadoPagoService;
use App\Services\PaymentGateway\PaymentManager;
use App\Services\Store\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function index()
    {
        $paymentGateways = PaymentGateway::with('configs')
            ->where('is_active', 1)
            ->get();

        $paypal = $paymentGateways->firstWhere('code', 'paypal');
        $paypalClientId = $paypal
            ? $paypal->getConfigValue('client_id', 'sandbox')
            : null;

        $stripe = $paymentGateways->firstWhere('code', 'stripe');
        $stripePublicKey = $stripe
            ? $stripe->getConfigValue('public_key', 'sandbox')
            : null;

        $mercadopago = $paymentGateways->firstWhere('code', 'mercadopago');
        $mercadopagoPublicKey = $mercadopago
            ? $mercadopago->getConfigValue('public_key', 'sandbox')
            : config('services.mercadopago.public_key');

        $cart = Session::get('cart', []);
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $shipping = null;
        $total = $subtotal + ($shipping ?? 0);

        return view('themes.xylo.checkout', compact(
            'cart',
            'subtotal',
            'shipping',
            'total',
            'paymentGateways',
            'paypalClientId',
            'stripePublicKey',
            'mercadopagoPublicKey'
        ));
    }

    public function process(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'suite' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:10',
            'zipcode' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'gateway' => 'required|string|in:mercadopago,paypal,stripe',
        ]);

        $cart = Session::get('cart', []);
        if ($cart === []) {
            return response()->json(['success' => false, 'message' => 'El carrito está vacío.'], 422);
        }

        $total = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

        $provinceName = config('store.provinces.'.$validated['state'], $validated['state']);

        Session::put('checkout.shipping', [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'address' => $validated['address'],
            'suite' => $validated['suite'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'state_name' => $provinceName,
            'postal_code' => $validated['zipcode'],
            'country' => $validated['country'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $environment = $this->mercadoPagoEnvironment();

        try {
            if ($validated['gateway'] === 'mercadopago') {
                $mp = PaymentManager::make('mercadopago', $environment);
                $preference = $mp->createPreference($cart, $total, Session::get('checkout.shipping'));
                $redirectUrl = $mp->resolveCheckoutUrl($preference);

                Session::put('checkout.mercadopago_reference', $preference['external_reference'] ?? null);

                if (! $redirectUrl) {
                    throw new \RuntimeException('Mercado Pago no devolvió URL de pago.');
                }

                return response()->json([
                    'success' => true,
                    'redirect' => $redirectUrl,
                ]);
            }

            if ($validated['gateway'] === 'paypal') {
                $paypal = PaymentManager::make('paypal', $environment);
                $order = $paypal->createOrder(number_format($total, 2, '.', ''), storeCurrencyCode());

                return response()->json([
                    'success' => true,
                    'gateway' => 'paypal',
                    'order' => $order,
                ]);
            }

            if ($validated['gateway'] === 'stripe') {
                $stripe = PaymentManager::make('stripe', $environment);
                $intent = $stripe->createPaymentIntent((int) round($total * 100), 'ars');

                return response()->json([
                    'success' => true,
                    'gateway' => 'stripe',
                    'client_secret' => $intent->client_secret ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment process failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json(['success' => false, 'message' => 'Método de pago no soportado.'], 422);
    }

    public function mercadoPagoSuccess(Request $request, OrderService $orderService)
    {
        $paymentId = $request->query('payment_id');

        if (! $paymentId) {
            return redirect()->route('checkout.index')->with('error', __('store.checkout.order_failed'));
        }

        try {
            $mp = PaymentManager::make('mercadopago', $this->mercadoPagoEnvironment());
            $payment = $mp->getPayment($paymentId);

            if (! in_array($payment['status'] ?? '', ['approved', 'authorized'], true)) {
                return redirect()->route('checkout.index')->with('error', __('store.checkout.order_failed'));
            }

            $orderService->createOrderFromCart('mercadopago', (string) $paymentId, (float) ($payment['transaction_amount'] ?? 0));

            return redirect()->route('checkout.success');
        } catch (\Exception $e) {
            Log::error('Mercado Pago success error: '.$e->getMessage());

            return redirect()->route('checkout.index')->with('error', $e->getMessage());
        }
    }

    public function mercadoPagoFailure()
    {
        return redirect()->route('checkout.index')->with('error', __('store.checkout.order_failed'));
    }

    public function mercadoPagoPending()
    {
        return redirect()->route('checkout.success')->with('success', __('store.checkout.order_pending'));
    }

    public function success()
    {
        return view('themes.xylo.checkout-success');
    }

    /**
     * PayPal success callback
     */
    public function paypalSuccess(Request $request, OrderService $orderService)
    {
        $orderId = $request->query('token');

        try {
            $paypal = PaymentManager::make('paypal', 'sandbox');
            $result = $paypal->captureOrder($orderId);

            if (($result['status'] ?? null) === 'COMPLETED') {
                $orderService->createOrderFromPaypal($result);

                return redirect()->route('checkout.success');
            }

            return redirect()->route('checkout.index')->with('error', __('store.checkout.order_failed'));
        } catch (\Exception $e) {
            Log::error('PayPal success error: '.$e->getMessage());

            return redirect()->route('checkout.index')->with('error', $e->getMessage());
        }
    }

    public function paypalCancel()
    {
        return redirect()->route('checkout.index')->with('error', __('store.checkout.order_failed'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'payment_method' => 'required',
        ]);

        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Cart is empty!');
        }

        $total = collect($cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $order = Order::create([
            'user_id' => Auth::id(),
            'address' => $request->address,
            'payment_method' => $request->payment_method,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'attributes' => json_encode($item['attributes']),
            ]);
        }

        Session::forget('cart');

        return redirect()->route('checkout.success')->with('success', __('store.checkout.order_success'));
    }

    private function mercadoPagoEnvironment(): string
    {
        $token = trim((string) config('services.mercadopago.access_token', ''));

        return MercadoPagoService::environmentForToken($token)
            ?? (config('app.env') === 'production' ? 'live' : 'sandbox');
    }
}
