<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the forgot password view (multi-step OTP).
     */
    public function create(Request $request): View
    {
        return view('auth.forgot-password', [
            'step'  => $request->query('step', 'send'),
            'email' => $request->query('email', ''),
        ]);
    }
}
