@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- PROFIL SAYA --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Profil Saya</h2>

        @if(session('status') === 'profile-updated')
            <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-xl text-sm">
                Profil berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
                    <input type="text" name="name"
                           value="{{ old('name', $user->name) }}"
                           required
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email"
                           value="{{ old('email', $user->email) }}"
                           required
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-5">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                        style="background:#0d2d52;">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- GANTI PASSWORD --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-lg font-semibold text-slate-800 mb-4">Ganti Password</h2>

        @if(session('status') === 'password-updated')
            <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-xl text-sm">
                Password berhasil diperbarui.
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password"
                           required autocomplete="current-password"
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                    <input type="password" name="password"
                           required autocomplete="new-password"
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation"
                           required autocomplete="new-password"
                           class="w-full rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>
            </div>

            <div class="mt-5">
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
                        style="background:#0d2d52;">
                    Perbarui Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
