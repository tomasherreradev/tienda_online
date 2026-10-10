<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('product_image_url')) {
    /**
     * URL pública de una imagen de producto almacenada en el disco public.
     */
    function product_image_url(?string $path, string $default = 'default.jpg'): string
    {
        if (empty($path)) {
            return Storage::url($default);
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        if (Storage::disk('public')->exists($normalized)) {
            return Storage::url($normalized);
        }

        return Storage::url($default);
    }
}
