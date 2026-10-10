<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function changeLanguage(Request $request)
    {
        $locale = storeLocale();

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return redirect()->back();
    }
}
