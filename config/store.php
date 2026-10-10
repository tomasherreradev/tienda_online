<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Idioma de la tienda
    |--------------------------------------------------------------------------
    */

    'locale' => env('STORE_LOCALE', 'es'),

    /*
    |--------------------------------------------------------------------------
    | Moneda de la tienda (pesos argentinos)
    |--------------------------------------------------------------------------
    */

    'currency' => env('STORE_CURRENCY', 'ARS'),

    /*
    |--------------------------------------------------------------------------
    | Envíos (Argentina)
    |--------------------------------------------------------------------------
    */

    'country_code' => 'AR',
    'country_name' => 'Argentina',

    'provinces' => [
        'CABA' => 'Ciudad Autónoma de Buenos Aires',
        'BA' => 'Buenos Aires',
        'CT' => 'Catamarca',
        'CC' => 'Chaco',
        'CH' => 'Chubut',
        'CB' => 'Córdoba',
        'CR' => 'Corrientes',
        'ER' => 'Entre Ríos',
        'FO' => 'Formosa',
        'JY' => 'Jujuy',
        'LP' => 'La Pampa',
        'LR' => 'La Rioja',
        'MZ' => 'Mendoza',
        'MI' => 'Misiones',
        'NQ' => 'Neuquén',
        'RN' => 'Río Negro',
        'SA' => 'Salta',
        'SJ' => 'San Juan',
        'SL' => 'San Luis',
        'SC' => 'Santa Cruz',
        'SF' => 'Santa Fe',
        'SE' => 'Santiago del Estero',
        'TF' => 'Tierra del Fuego',
        'TM' => 'Tucumán',
    ],

];
