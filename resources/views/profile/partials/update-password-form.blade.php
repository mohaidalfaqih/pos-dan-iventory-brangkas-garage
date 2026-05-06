<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Ganti Password') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Kode verifikasi akan dikirim ke email Anda sebelum password dapat diubah.
        </p>
    </header>

    {{-- STEP 1: Tombol kirim kode OTP --}}
    @if (!session('otp_sent_password'))
        <form method="post" action="{{ route('verification.send-password-code') }}" class="mt-6">
            @csrf
            <p class="text-sm text-gray-700 mb-4">
                Klik tombol di bawah untuk mengirim kode verifikasi ke email Anda:
                <strong>{{ auth()->user()->email }}</strong>
            </p>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Kirim Kode Verifikasi
            </button>
        </form>
    @endif

    {{-- STEP 2: Form ganti password setelah OTP dikirim --}}
    @if (session('otp_sent_password'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">
                ✅ Kode verifikasi telah dikirim ke <strong>{{ auth()->user()->email }}</strong>.
                Periksa inbox atau folder spam Anda.
            </p>
        </div>

        <form method="post" action="{{ route('verification.update-password') }}" class="mt-6 space-y-6">
            @csrf
            @method('put')

            <div>
                <x-input-label for="otp_code" value="Kode Verifikasi (6 digit)" />
                <x-text-input id="otp_code" name="otp_code" type="text"
                    class="mt-1 block w-full tracking-widest text-center text-lg font-bold"
                    maxlength="6" placeholder="000000" autocomplete="off" />
                <x-input-error :messages="$errors->updatePassword->get('otp_code')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('Password Baru')" />
                <x-text-input id="update_password_password" name="password" type="password"
                    class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Password Baru')" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation"
                    type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>Simpan Password</x-primary-button>

                {{-- Kirim ulang kode --}}
                <form method="post" action="{{ route('verification.send-password-code') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:underline">
                        Kirim ulang kode
                    </button>
                </form>

                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition
                        x-init="setTimeout(() => show = false, 3000)"
                        class="text-sm text-green-600 font-medium">
                        ✅ Password berhasil diperbarui.
                    </p>
                @endif
            </div>
        </form>
    @endif
</section>
