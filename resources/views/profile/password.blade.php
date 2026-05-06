@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Ganti Password</h1>
        <a href="{{ auth()->user()->role === 'kasir' ? route('pos.index') : route('dashboard') }}"
           class="px-4 py-2 rounded-xl bg-white shadow hover:bg-slate-50 text-sm font-semibold transition">
            ← Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">

        @php $step = request('step', 'send'); @endphp

        {{-- Sukses --}}
        @if(session('status') === 'password-updated')
            <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
                ✅ Password berhasil diperbarui.
            </div>
        @endif

        {{-- ══════════════════════════════════════ --}}
        {{-- STEP 1: Kirim kode OTP                --}}
        {{-- ══════════════════════════════════════ --}}
        @if($step === 'send')
            <p class="text-sm text-slate-600 mb-5">
                Kode verifikasi 6 digit akan dikirim ke email Anda:<br>
                <strong class="text-slate-800">{{ auth()->user()->email }}</strong>
            </p>

            <form method="POST" action="{{ route('verification.send-password-code') }}">
                @csrf
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                        style="background:#184E77;">
                    Kirim Kode Verifikasi
                </button>
            </form>

        {{-- ══════════════════════════════════════ --}}
        {{-- STEP 2: Input & verifikasi kode OTP   --}}
        {{-- ══════════════════════════════════════ --}}
        @elseif($step === 'input_code')

            <div class="mb-5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
                 Kode verifikasi dikirim ke <strong>{{ auth()->user()->email }}</strong>.
                Cek inbox atau folder spam. Berlaku <strong>10 menit</strong>.
            </div>

            @if($errors->verifyPassword->has('otp_code'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
                    ❌ {{ $errors->verifyPassword->first('otp_code') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.verify-password-code') }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Masukkan Kode Verifikasi (6 digit)
                    </label>
                    <input type="text" name="otp_code"
                           maxlength="6" placeholder="000000"
                           autocomplete="off"
                           value="{{ old('otp_code') }}"
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-center tracking-[0.5em] text-xl font-bold py-3">
                </div>

                <div class="mt-5 flex items-center gap-4">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                            style="background:#0d2d52;">
                         Verifikasi Kode
                    </button>

                    <form method="POST" action="{{ route('verification.send-password-code') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-blue-600 hover:underline">
                            Kirim ulang kode
                        </button>
                    </form>
                </div>
            </form>

        {{-- ══════════════════════════════════════ --}}
        {{-- STEP 3: Kode valid → input password   --}}
        {{-- ══════════════════════════════════════ --}}
        @elseif($step === 'input_password')

            <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">
                ✅ Kode verifikasi valid! Silakan masukkan password baru Anda.
            </div>

            <form method="POST" action="{{ route('verification.update-password') }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" name="password" id="new_password"
                                   required autocomplete="new-password"
                                   class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm pr-10">
                            <button type="button" onclick="togglePw('new_password','eye1')"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                                <svg id="eye1" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                        @error('password', 'updatePassword')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="confirm_password"
                                   required autocomplete="new-password"
                                   class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm pr-10">
                            <button type="button" onclick="togglePw('confirm_password','eye2')"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600">
                                <svg id="eye2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                            style="background:#0d2d52;">
                         Simpan Password Baru
                    </button>
                </div>
            </form>

        @endif

    </div>
</div>

<script>
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const show  = input.type === 'password';
    input.type  = show ? 'text' : 'password';
    icon.innerHTML = show
        ? '<path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/><path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z"/>'
        : '<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>';
}
</script>
@endsection
