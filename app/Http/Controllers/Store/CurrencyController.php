<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function changeCurrency(Request $request)
    {
        session(['currency' => storeCurrencyCode()]);

        return back();
    }
}
