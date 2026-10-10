<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('payment_gateways')->where('code', 'mercadopago')->exists();

        if ($exists) {
            return;
        }

        $gatewayId = DB::table('payment_gateways')->insertGetId([
            'name' => 'Mercado Pago',
            'code' => 'mercadopago',
            'description' => 'Tarjetas argentinas, débito, billetera y cuotas',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payment_gateway_configs')->insert([
            [
                'gateway_id' => $gatewayId,
                'key_name' => 'access_token',
                'key_value' => env('MERCADOPAGO_ACCESS_TOKEN', 'your-mercadopago-access-token'),
                'is_encrypted' => true,
                'environment' => 'sandbox',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway_id' => $gatewayId,
                'key_name' => 'public_key',
                'key_value' => env('MERCADOPAGO_PUBLIC_KEY', 'your-mercadopago-public-key'),
                'is_encrypted' => false,
                'environment' => 'sandbox',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway_id' => $gatewayId,
                'key_name' => 'access_token',
                'key_value' => env('MERCADOPAGO_ACCESS_TOKEN', 'your-mercadopago-access-token'),
                'is_encrypted' => true,
                'environment' => 'live',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'gateway_id' => $gatewayId,
                'key_name' => 'public_key',
                'key_value' => env('MERCADOPAGO_PUBLIC_KEY', 'your-mercadopago-public-key'),
                'is_encrypted' => false,
                'environment' => 'live',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        $gatewayId = DB::table('payment_gateways')->where('code', 'mercadopago')->value('id');

        if ($gatewayId) {
            DB::table('payment_gateway_configs')->where('gateway_id', $gatewayId)->delete();
            DB::table('payment_gateways')->where('id', $gatewayId)->delete();
        }
    }
};
