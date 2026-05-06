<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Perbarui nama akun Anda. Untuk mengubah email, kode verifikasi akan dikirim ke email Anda saat ini.
        </p>
    </header>

    {{-- Form update nama --}}
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan Nama</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 font-medium">
                    ✅ Nama berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>

    <hr class="my-8 border-gray-200">

    {{-- Bagian ubah email --}}
    <div>
        <h3 class="text-base font-medium text-gray-900">Ubah Email</h3>
        <p class="mt-1 text-sm text-gray-600">
            Email saat ini: <strong>{{ $user->email }}</strong>
        </p>

        @if (session('status') === 'email-updated')
            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm text-green-700">✅ Email berhasil diperbarui.</p>
            </div>
        @endif

        {{-- STEP 1: Form input email baru --}}
        @if (!session('otp_sent_email'))
            <form method="post" action="{{ route('verification.send-email-code') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <x-input-label for="new_email" value="Email Baru" />
                    <x-text-input id="new_email" name="new_email" type="email"
                        class="mt-1 block w-full"
                        :value="old('new_email')"
                        placeholder="emailbaru@gmail.com"
                        autocomplete="email" />
                    <x-input-error class="mt-2" :messages="$errors->get('new_email')" />
                </div>

                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Kirim Kode Verifikasi ke Email Lama
                </button>
            </form>
        @endif

        {{-- STEP 2: Form verifikasi OTP setelah kode dikirim --}}
        @if (session('otp_sent_email'))
            <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                <p class="text-sm text-green-700">
                    ✅ Kode verifikasi telah dikirim ke <strong>{{ $user->email }}</strong>.
                    Periksa inbox atau folder spam Anda.
                </p>
            </div>

            <form method="post" action="{{ route('verification.update-email') }}" class="mt-4 space-y-4">
                @csrf
                @method('put')

                <input type="hidden" name="new_email" value="{{ session('pending_new_email') }}">

                <p class="text-sm text-gray-700">
                    Email baru yang akan digunakan:
                    <strong>{{ session('pending_new_email') }}</strong>
                </p>

                <div>
                    <x-input-label for="otp_email_code" value="Kode Verifikasi (6 digit)" />
                    <x-text-input id="otp_email_code" name="otp_code" type="text"
                        class="mt-1 block w-full tracking-widest text-center text-lg font-bold"
                        maxlength="6" placeholder="000000" autocomplete="off" />
                    <x-input-error :messages="$errors->updateEmail->get('otp_code')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4">
                    <x-primary-button>Konfirmasi Ubah Email</x-primary-button>

                    {{-- Kirim ulang kode --}}
                    <form method="post" action="{{ route('verification.send-email-code') }}" class="inline">
                        @csrf
                        <input type="hidden" name="new_email" value="{{ session('pending_new_email') }}">
                        <button type="submit" class="text-sm text-blue-600 hover:underline">
                            Kirim ulang kode
                        </button>
                    </form>
                </div>
            </form>
        @endif
    </div>
</section>
