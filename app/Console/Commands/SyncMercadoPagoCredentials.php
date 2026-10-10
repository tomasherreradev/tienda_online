<?php

namespace App\Console\Commands;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Console\Command;

class SyncMercadoPagoCredentials extends Command
{
    protected $signature = 'mercadopago:sync-env';

    protected $description = 'Copia MERCADOPAGO_* del .env a la pasarela Mercado Pago en la base de datos';

    public function handle(): int
    {
        $token = trim((string) config('services.mercadopago.access_token', ''));
        $publicKey = trim((string) config('services.mercadopago.public_key', ''));

        if ($token === '') {
            $this->error('MERCADOPAGO_ACCESS_TOKEN no está definido en .env');

            return self::FAILURE;
        }

        $gateway = PaymentGateway::where('code', 'mercadopago')->first();

        if (! $gateway) {
            $this->error('No existe la pasarela mercadopago. Ejecutá las migraciones.');

            return self::FAILURE;
        }

        foreach (['sandbox', 'live'] as $environment) {
            PaymentGatewayConfig::updateOrCreate(
                [
                    'gateway_id' => $gateway->id,
                    'key_name' => 'access_token',
                    'environment' => $environment,
                ],
                ['is_encrypted' => true, 'key_value' => $token]
            );

            if ($publicKey !== '') {
                PaymentGatewayConfig::updateOrCreate(
                    [
                        'gateway_id' => $gateway->id,
                        'key_name' => 'public_key',
                        'environment' => $environment,
                    ],
                    ['is_encrypted' => false, 'key_value' => $publicKey]
                );
            }
        }

        $this->info('Credenciales de Mercado Pago sincronizadas desde .env');

        return self::SUCCESS;
    }
}
