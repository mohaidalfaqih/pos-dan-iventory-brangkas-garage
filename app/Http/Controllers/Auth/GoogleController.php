<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Redirect ke halaman login Google.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle callback dari Google setelah login.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Login Google gagal: ' . $e->getMessage()]);
        }

        // Cek apakah email terdaftar di sistem
        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Email ' . $googleUser->getEmail() . ' tidak terdaftar di sistem. Hubungi admin.']);
        }

        // Update google_id dan avatar
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar'    => $googleUser->getAvatar(),
        ]);

        Auth::login($user, true);

        // Redirect sesuai role
        if ($user->role === 'kasir') {
            return redirect()->route('pos.index');
        }

        return redirect()->route('dashboard');
    }
}
