@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-800">Profil Saya</h1>
        <a href="{{ auth()->user()->role === 'kasir' ? route('pos.index') : route('dashboard') }}"
           class="px-4 py-2 rounded-xl bg-white shadow hover:bg-slate-50 text-sm font-semibold transition">
            ← Kembali
        </a>
    </div>

    @php
        $step     = request('step', 'send');
        $newEmail = request('new_email', session('pending_new_email'));
    @endphp

    {{-- ══════════════════════════════════════ --}}
    {{-- UBAH NAMA                              --}}
    {{-- ══════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-4">Ubah Nama</h2>

        @if(session('status') === 'profile-updated')
            <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
                ✅ Nama berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
                <input type="text" name="name"
                       value="{{ old('name', $user->name) }}"
                       required
                       class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="mt-5">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                        style="background:#0d2d52;">
                    Simpan Nama
                </button>
            </div>
        </form>
    </div>

    {{-- ══════════════════════════════════════ --}}
    {{-- UBAH EMAIL                             --}}
    {{-- ══════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-base font-semibold text-slate-800 mb-1">Ubah Email</h2>
        <p class="text-sm text-slate-500 mb-4">
            Email saat ini: <strong class="text-slate-700">{{ $user->email }}</strong>
        </p>

        @if(session('status') === 'email-updated')
            <div class="mb-4 px-4 py-3 bg-green-100 text-green-700 rounded-xl text-sm font-medium">
                ✅ Email berhasil diperbarui.
            </div>
        @endif

        {{-- STEP 1: Input email baru --}}
        @if($step === 'send')
            <form method="POST" action="{{ route('verification.send-email-code') }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email Baru</label>
                    <input type="email" name="new_email"
                           value="{{ old('new_email') }}"
                           placeholder="emailbaru@gmail.com"
                           required
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('new_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mt-5">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                            style="background:#184E77;">
                         Kirim Kode Verifikasi ke Email Lama
                    </button>
                </div>
            </form>

        {{-- STEP 2: Input & verifikasi kode OTP --}}
        @elseif($step === 'input_code')

            <div class="mb-5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
                 Kode verifikasi dikirim ke <strong>{{ $user->email }}</strong>.
                Cek inbox atau folder spam. Berlaku <strong>10 menit</strong>.
            </div>

            @if($errors->verifyEmail->has('otp_code'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
                    ❌ {{ $errors->verifyEmail->first('otp_code') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.verify-email-code') }}">
                @csrf
                <input type="hidden" name="new_email" value="{{ $newEmail }}">

                <p class="text-sm text-slate-600 mb-4">
                    Email baru: <strong>{{ $newEmail }}</strong>
                </p>

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

                    <form method="POST" action="{{ route('verification.send-email-code') }}" class="inline">
                        @csrf
                        <input type="hidden" name="new_email" value="{{ $newEmail }}">
                        <button type="submit" class="text-sm text-blue-600 hover:underline">
                            Kirim ulang kode
                        </button>
                    </form>
                </div>
            </form>

        {{-- STEP 3: Kode valid → konfirmasi --}}
        @elseif($step === 'confirm')

            <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">
                ✅ Kode verifikasi valid! Konfirmasi untuk mengubah email ke
                <strong>{{ $newEmail }}</strong>.
            </div>

            <form method="POST" action="{{ route('verification.update-email') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="new_email" value="{{ $newEmail }}">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                        style="background:#0d2d52;">
                    Konfirmasi Ubah Email
                </button>
            </form>

        @endif
    </div>

</div>
@endsection
