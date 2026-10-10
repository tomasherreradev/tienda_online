<?php

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

if (! function_exists('external_http')) {
    /**
     * Cliente HTTP para APIs externas con CA bundle en Windows/local.
     */
    function external_http(): PendingRequest
    {
        $request = Http::timeout(30);

        $verify = config('services.http.verify_ssl', true);

        if ($verify === false || $verify === 'false' || $verify === '0') {
            return $request->withOptions(['verify' => false]);
        }

        $caBundle = config('services.http.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '' && file_exists($caBundle)) {
            return $request->withOptions(['verify' => $caBundle]);
        }

        return $request;
    }
}
