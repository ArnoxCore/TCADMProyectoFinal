<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AuthViewController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function register(): View
    {
        return view('auth.register');
    }
}
