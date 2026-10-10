<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const USD_TO_ARS = 1580;

    public function up(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (Schema::hasTable('store_settings')) {
            $alreadyConverted = DB::table('store_settings')
                ->where('key', 'prices_converted_usd_to_ars')
                ->where('value', '1')
                ->exists();

            if ($alreadyConverted) {
                return;
            }
        }

        $rate = self::USD_TO_ARS;

        DB::table('product_variants')->orderBy('id')->chunkById(500, function ($variants) use ($rate) {
            foreach ($variants as $variant) {
                $updates = [
                    'price' => round((float) $variant->price * $rate, 2),
                    'updated_at' => now(),
                ];

                if ($variant->discount_price !== null) {
                    $updates['discount_price'] = round((float) $variant->discount_price * $rate, 2);
                }

                DB::table('product_variants')->where('id', $variant->id)->update($updates);
            }
        });

        if (Schema::hasColumn('products', 'price')) {
            DB::table('products')
                ->whereNotNull('price')
                ->update([
                    'price' => DB::raw('ROUND(price * '.self::USD_TO_ARS.', 2)'),
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('store_settings')) {
            DB::table('store_settings')->updateOrInsert(
                ['key' => 'prices_converted_usd_to_ars'],
                [
                    'value' => '1',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('store_settings')->updateOrInsert(
                ['key' => 'usd_to_ars_rate'],
                [
                    'value' => (string) self::USD_TO_ARS,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (Schema::hasTable('store_settings')) {
            $converted = DB::table('store_settings')
                ->where('key', 'prices_converted_usd_to_ars')
                ->where('value', '1')
                ->exists();

            if (! $converted) {
                return;
            }
        }

        $rate = self::USD_TO_ARS;

        DB::table('product_variants')->orderBy('id')->chunkById(500, function ($variants) use ($rate) {
            foreach ($variants as $variant) {
                $updates = [
                    'price' => round((float) $variant->price / $rate, 2),
                    'updated_at' => now(),
                ];

                if ($variant->discount_price !== null) {
                    $updates['discount_price'] = round((float) $variant->discount_price / $rate, 2);
                }

                DB::table('product_variants')->where('id', $variant->id)->update($updates);
            }
        });

        if (Schema::hasColumn('products', 'price')) {
            DB::table('products')
                ->whereNotNull('price')
                ->update([
                    'price' => DB::raw('ROUND(price / '.self::USD_TO_ARS.', 2)'),
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('store_settings')) {
            DB::table('store_settings')->where('key', 'prices_converted_usd_to_ars')->delete();
            DB::table('store_settings')->where('key', 'usd_to_ars_rate')->delete();
        }
    }
};
