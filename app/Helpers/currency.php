<?php

use App\Models\Currency;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('storeLocale')) {
    function storeLocale(): string
    {
        return config('store.locale', 'es');
    }
}

if (! function_exists('storeCurrencyCode')) {
    function storeCurrencyCode(): string
    {
        return config('store.currency', 'ARS');
    }
}

if (! function_exists('convert_price')) {
    function convert_price($amount, $currencyCode = null)
    {
        return round((float) $amount, 2);
    }
}

if (! function_exists('currency_to_usd')) {
    function currency_to_usd($amount, $fromCurrency)
    {
        return round((float) $amount, 2);
    }
}

if (! function_exists('getWebConfig')) {
    function getWebConfig($key, $default = null)
    {
        if ($key === 'default_currency' && $default === null) {
            $default = storeCurrencyCode();
        }

        return Cache::rememberForever("store_setting_{$key}", function () use ($key, $default) {
            return StoreSetting::where('key', $key)->value('value') ?? $default;
        });
    }
}

if (! function_exists('activeCurrency')) {
    function activeCurrency()
    {
        $code = storeCurrencyCode();

        return Cache::rememberForever('active_currency_'.$code, function () use ($code) {
            return Currency::where('code', $code)->first()
                ?? new Currency([
                    'code' => $code,
                    'symbol' => '$',
                    'name' => 'Peso argentino',
                    'exchange_rate' => 1,
                ]);
        });
    }
}
