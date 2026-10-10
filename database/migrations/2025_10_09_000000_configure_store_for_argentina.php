<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('languages')) {
            DB::table('languages')->update(['active' => false]);

            $spanish = [
                'name' => 'Spanish',
                'translated_text' => 'Español',
                'active' => true,
                'updated_at' => now(),
            ];

            if (DB::table('languages')->where('code', 'es')->exists()) {
                DB::table('languages')->where('code', 'es')->update($spanish);
            } else {
                DB::table('languages')->insert(array_merge(['code' => 'es', 'created_at' => now()], $spanish));
            }
        }

        if (Schema::hasTable('currencies')) {
            DB::table('currencies')->delete();
            DB::table('currencies')->insert([
                'name' => 'Peso argentino',
                'code' => 'ARS',
                'symbol' => '$',
                'exchange_rate' => 1.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('store_settings')) {
            DB::table('store_settings')->updateOrInsert(
                ['key' => 'default_currency'],
                ['value' => 'ARS', 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Sin reversión: la tienda está pensada solo para ARS y español.
    }
};
