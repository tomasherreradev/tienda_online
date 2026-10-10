<?php

namespace App\Services\PaymentGateway;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use App\Models\Product;
use Illuminate\Support\Str;

class MercadoPagoService
{
    protected string $accessToken;

    protected ?string $publicKey;

    protected string $baseUrl = 'https://api.mercadopago.com';

    public function __construct(string $environment = 'sandbox')
    {
        $gateway = PaymentGateway::where('code', 'mercadopago')
            ->where('is_active', true)
            ->firstOrFail();

        [$this->accessToken, $this->publicKey] = self::resolveCredentials($gateway->id, $environment);

        if ($this->accessToken === '' || str_starts_with($this->accessToken, 'your-')) {
            throw new \RuntimeException(
                'Mercado Pago: configurá MERCADOPAGO_ACCESS_TOKEN en .env o access_token en Admin → Pasarelas de pago.'
            );
        }

        if (! str_starts_with($this->accessToken, 'TEST-') && ! str_starts_with($this->accessToken, 'APP_USR-')) {
            throw new \RuntimeException(
                'Mercado Pago: el access token debe comenzar con TEST- (pruebas) o APP_USR- (producción).'
            );
        }
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    public static function resolveCredentials(int $gatewayId, string $environment): array
    {
        $envToken = trim((string) config('services.mercadopago.access_token', ''));
        $envPublic = trim((string) config('services.mercadopago.public_key', ''));

        if ($envToken !== '' && ! str_starts_with($envToken, 'your-')) {
            return [$envToken, $envPublic !== '' && ! str_starts_with($envPublic, 'your-') ? $envPublic : null];
        }

        $preferredEnv = self::environmentForToken($envToken) ?? $environment;

        $configs = PaymentGatewayConfig::mapForGateway($gatewayId, $preferredEnv);

        if (($configs['access_token'] ?? '') === '' || str_starts_with($configs['access_token'] ?? '', 'your-')) {
            $configs = PaymentGatewayConfig::mapForGateway($gatewayId, $environment);
        }

        if (($configs['access_token'] ?? '') === '' || str_starts_with($configs['access_token'] ?? '', 'your-')) {
            $configs = PaymentGatewayConfig::mapForGateway($gatewayId, null);
        }

        return [
            $configs['access_token'] ?? '',
            $configs['public_key'] ?? null,
        ];
    }

    public static function environmentForToken(string $token): ?string
    {
        if (str_starts_with($token, 'TEST-')) {
            return 'sandbox';
        }

        if (str_starts_with($token, 'APP_USR-')) {
            return 'live';
        }

        return null;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * @param  array<string, mixed>  $cart
     * @param  array<string, mixed>  $shipping
     */
    public function createPreference(array $cart, float $total, array $shipping): array
    {
        $items = [];
        foreach ($cart as $item) {
            $product = Product::with('translation')->find($item['product_id']);
            $title = $product?->translation?->name ?? $item['variant_name'] ?? 'Producto';
            $items[] = [
                'title' => Str::limit($title, 256),
                'quantity' => (int) $item['quantity'],
                'unit_price' => round((float) $item['price'], 2),
                'currency_id' => storeCurrencyCode(),
            ];
        }

        if ($items === []) {
            throw new \InvalidArgumentException('El carrito está vacío.');
        }

        $reference = 'velstore-'.Str::uuid()->toString();

        $payload = [
            'items' => $items,
            'payer' => [
                'name' => trim(($shipping['first_name'] ?? '').' '.($shipping['last_name'] ?? '')),
                'email' => $shipping['email'] ?? null,
                'phone' => [
                    'number' => $shipping['phone'] ?? null,
                ],
                'address' => [
                    'zip_code' => $shipping['postal_code'] ?? null,
                    'street_name' => $shipping['address'] ?? null,
                ],
            ],
            'back_urls' => [
                'success' => route('mercadopago.success'),
                'failure' => route('mercadopago.failure'),
                'pending' => route('mercadopago.pending'),
            ],
            'external_reference' => $reference,
            'statement_descriptor' => Str::limit(config('app.name', 'Tejada Hogar'), 22),
        ];

        if (self::shouldUseAutoReturn()) {
            $payload['auto_return'] = 'approved';
        }

        $response = external_http()->withToken($this->accessToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/checkout/preferences", $payload);

        if (! $response->successful()) {
            $message = $response->json('message')
                ?? collect($response->json('cause'))->pluck('description')->filter()->first()
                ?? $response->body();

            if (str_contains(strtolower((string) $message), 'unauthorized')) {
                $message .= ' Revisá que MERCADOPAGO_ACCESS_TOKEN en .env sea el Access Token (no la Public Key) y que coincida con TEST- o APP_USR-.';
            }

            throw new \RuntimeException('Mercado Pago: '.$message);
        }

        $data = $response->json();
        $data['external_reference'] = $reference;

        return $data;
    }

    public function getPayment(string $paymentId): array
    {
        $response = external_http()->withToken($this->accessToken)
            ->acceptJson()
            ->get("{$this->baseUrl}/v1/payments/{$paymentId}");

        if (! $response->successful()) {
            throw new \RuntimeException('No se pudo verificar el pago en Mercado Pago.');
        }

        return $response->json();
    }

    public function resolveCheckoutUrl(array $preference): ?string
    {
        if (str_starts_with($this->accessToken, 'TEST-')) {
            return $preference['sandbox_init_point'] ?? $preference['init_point'] ?? null;
        }

        return $preference['init_point'] ?? $preference['sandbox_init_point'] ?? null;
    }

    protected static function shouldUseAutoReturn(): bool
    {
        $url = config('app.url', '');

        return str_starts_with($url, 'https://');
    }
}
