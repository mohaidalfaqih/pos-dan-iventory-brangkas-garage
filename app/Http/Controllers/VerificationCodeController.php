<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\VerificationCodeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VerificationCodeController extends Controller
{
    // ─────────────────────────────────────────
    //  GANTI PASSWORD (auth)
    // ─────────────────────────────────────────

    /** STEP 1 – Kirim kode OTP ke email untuk ganti password */
    public function sendPasswordCode(Request $request): RedirectResponse
    {
        $user = $request->user();

        VerificationCode::where('user_id', $user->id)
            ->where('type', 'password_change')
            ->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'user_id'    => $user->id,
            'type'       => 'password_change',
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new VerificationCodeNotification($code, 'password_change'));

        return redirect()->route('profile.password', ['step' => 'input_code'])
            ->with('otp_sent_password', true);
    }

    /** STEP 2 – Verifikasi kode OTP */
    public function verifyPasswordCode(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user   = $request->user();
        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', 'password_change')
            ->where('used', false)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid() || $record->code !== $request->otp_code) {
            return redirect()->route('profile.password', ['step' => 'input_code'])
                ->with('otp_sent_password', true)
                ->withErrors(['otp_code' => 'Kode verifikasi salah atau sudah kadaluarsa.'], 'verifyPassword');
        }

        $record->update(['verified' => true]);

        return redirect()->route('profile.password', ['step' => 'input_password'])
            ->with('otp_verified_password', true);
    }

    /** STEP 3 – Simpan password baru */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('updatePassword', [
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user   = $request->user();
        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', 'password_change')
            ->where('used', false)
            ->where('verified', true)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid()) {
            return redirect()->route('profile.password')
                ->withErrors(['otp_code' => 'Sesi kode verifikasi kadaluarsa. Ulangi dari awal.'], 'updatePassword');
        }

        $record->update(['used' => true]);
        $user->update(['password' => Hash::make($request->password)]);
        $request->session()->put('password_hash_web', $user->getAuthPassword());

        return redirect()->route('profile.password')->with('status', 'password-updated');
    }

    // ─────────────────────────────────────────
    //  UBAH EMAIL (auth)
    // ─────────────────────────────────────────

    /** STEP 1 – Kirim kode OTP ke email lama */
    public function sendEmailCode(Request $request): RedirectResponse
    {
        $request->validate([
            'new_email' => ['required', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
        ]);

        $user = $request->user();

        VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_change')
            ->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'user_id'    => $user->id,
            'type'       => 'email_change',
            'code'       => $code,
            'new_email'  => $request->new_email,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new VerificationCodeNotification($code, 'email_change', $request->new_email));

        return redirect()->route('profile.edit', ['step' => 'input_code', 'new_email' => $request->new_email])
            ->with('otp_sent_email', true)
            ->with('pending_new_email', $request->new_email);
    }

    /** STEP 2 – Verifikasi kode OTP email */
    public function verifyEmailCode(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_code'  => ['required', 'string', 'size:6'],
            'new_email' => ['required', 'email'],
        ]);

        $user   = $request->user();
        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_change')
            ->where('new_email', $request->new_email)
            ->where('used', false)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid() || $record->code !== $request->otp_code) {
            return redirect()->route('profile.edit', ['step' => 'input_code', 'new_email' => $request->new_email])
                ->with('otp_sent_email', true)
                ->with('pending_new_email', $request->new_email)
                ->withErrors(['otp_code' => 'Kode verifikasi salah atau sudah kadaluarsa.'], 'verifyEmail');
        }

        $record->update(['verified' => true]);

        return redirect()->route('profile.edit', ['step' => 'confirm', 'new_email' => $request->new_email])
            ->with('otp_verified_email', true)
            ->with('pending_new_email', $request->new_email);
    }

    /** STEP 3 – Simpan email baru */
    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validateWithBag('updateEmail', [
            'new_email' => ['required', 'email'],
        ]);

        $user   = $request->user();
        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_change')
            ->where('new_email', $request->new_email)
            ->where('used', false)
            ->where('verified', true)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid()) {
            return redirect()->route('profile.edit')
                ->withErrors(['otp_code' => 'Sesi kode verifikasi kadaluarsa. Ulangi dari awal.'], 'updateEmail');
        }

        $record->update(['used' => true]);
        $user->update([
            'email'             => $request->new_email,
            'email_verified_at' => null,
        ]);

        return redirect()->route('profile.edit')->with('status', 'email-updated');
    }

    // ─────────────────────────────────────────
    //  LUPA PASSWORD (guest)
    // ─────────────────────────────────────────

    /** STEP 1 – Kirim kode OTP ke email (guest) */
    public function sendForgotPasswordCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Email tidak terdaftar di sistem.',
        ]);

        $user = User::where('email', $request->email)->first();

        VerificationCode::where('user_id', $user->id)
            ->where('type', 'forgot_password')
            ->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationCode::create([
            'user_id'    => $user->id,
            'type'       => 'forgot_password',
            'code'       => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $user->notify(new VerificationCodeNotification($code, 'forgot_password'));

        return redirect()->route('password.request', ['step' => 'verify', 'email' => $request->email]);
    }

    /** STEP 2 – Verifikasi kode OTP (guest) */
    public function verifyForgotPasswordCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', 'forgot_password')
            ->where('used', false)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid() || $record->code !== $request->otp_code) {
            return redirect()->route('password.request', ['step' => 'verify', 'email' => $request->email])
                ->withErrors(['otp_code' => 'Kode verifikasi salah atau sudah kadaluarsa.'], 'verifyForgot');
        }

        $record->update(['verified' => true]);

        return redirect()->route('password.request', ['step' => 'reset', 'email' => $request->email]);
    }

    /** STEP 3 – Reset password baru (guest) */
    public function resetForgotPassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('resetForgot', [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $record = VerificationCode::where('user_id', $user->id)
            ->where('type', 'forgot_password')
            ->where('used', false)
            ->where('verified', true)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid()) {
            return redirect()->route('password.request')
                ->withErrors(['otp_code' => 'Sesi kode verifikasi kadaluarsa. Ulangi dari awal.'], 'resetForgot');
        }

        $record->update(['used' => true]);
        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login.');
    }
}
