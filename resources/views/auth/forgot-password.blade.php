<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — {{ config('app.name', 'Brangkas Garage') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: ui-sans-serif, system-ui, sans-serif; }

        body {
            min-height: 100vh;
            background: #0d2d52;
            display: flex;
            align-items: stretch;
        }

        .login-wrapper {
            width: 100%;
            min-height: 100vh;
            position: relative;
            display: flex;
        }

        .bg-svg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
        }

        .left-panel {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .right-panel {
            width: 42%;
            min-width: 340px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 3rem;
            position: relative;
            z-index: 1;
        }

        .form-box { width: 100%; max-width: 340px; }

        .form-title {
            color: #ffffff;
            font-size: 1.7rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.4rem;
        }

        .form-subtitle {
            color: #7bafd4;
            font-size: 0.75rem;
            text-align: center;
            margin-bottom: 1.6rem;
            line-height: 1.5;
        }

        /* Step indicator */
        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 1.6rem;
        }
        .step-dot {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: 700;
            background: rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.4);
            border: 2px solid rgba(255,255,255,0.15);
            transition: all 0.2s;
        }
        .step-dot.active {
            background: #1e5fa8;
            color: #fff;
            border-color: #4a90d9;
        }
        .step-dot.done {
            background: #16a34a;
            color: #fff;
            border-color: #22c55e;
        }
        .step-line {
            width: 36px; height: 2px;
            background: rgba(255,255,255,0.12);
        }
        .step-line.done { background: #22c55e; }

        .field-group { margin-bottom: 1rem; }

        .field-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 500;
            color: #a8c8e0;
            margin-bottom: 0.28rem;
        }

        .field-wrap { position: relative; }

        .field-icon {
            position: absolute;
            left: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6a9ec0;
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .field-input {
            width: 100%;
            padding: 0.6rem 0.9rem 0.6rem 2.3rem;
            border-radius: 0.4rem;
            border: none;
            background: #ffffff;
            color: #1a3a5c;
            font-size: 0.83rem;
            outline: none;
            transition: box-shadow 0.2s;
        }
        .field-input::placeholder { color: #7ab0d4; }
        .field-input:focus { box-shadow: 0 0 0 2px #4a90d9; }
        .field-input.otp-input {
            padding: 0.7rem 0.9rem;
            text-align: center;
            letter-spacing: 0.5em;
            font-size: 1.3rem;
            font-weight: 700;
        }
        .field-input.pw-input { padding-right: 2.5rem; }

        .toggle-pw {
            position: absolute;
            right: 0.7rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #6a9ec0;
            display: flex;
            align-items: center;
            padding: 0;
        }
        .toggle-pw:hover { color: #a8d4f0; }

        .field-error { margin-top: 0.22rem; font-size: 0.7rem; color: #f87171; }

        .alert-info {
            margin-bottom: 1rem;
            padding: 0.6rem 0.8rem;
            border-radius: 0.4rem;
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(59,130,246,0.3);
            color: #93c5fd;
            font-size: 0.72rem;
            line-height: 1.5;
        }
        .alert-success {
            margin-bottom: 1rem;
            padding: 0.6rem 0.8rem;
            border-radius: 0.4rem;
            background: rgba(22,163,74,0.15);
            border: 1px solid rgba(22,163,74,0.3);
            color: #86efac;
            font-size: 0.72rem;
        }

        .btn-primary {
            display: block;
            width: 100%;
            margin-top: 1.2rem;
            padding: 0.6rem 1rem;
            border-radius: 0.38rem;
            border: none;
            background: #1e5fa8;
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-primary:hover { opacity: 0.85; }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 0.9rem;
            font-size: 0.7rem;
            color: #7bafd4;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }

        .resend-btn {
            background: none;
            border: none;
            color: #7bafd4;
            font-size: 0.7rem;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            margin-top: 0.5rem;
            display: block;
            text-align: center;
            width: 100%;
        }
        .resend-btn:hover { color: #a8d4f0; }

        @media (max-width: 680px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; }
            #circles-left { display: none; }
            #circles-right { transform: translateX(-170px); }
            .form-box {
                border: 1.5px solid rgba(255,255,255,0.2);
                border-radius: 1.5rem;
                padding: 1.5rem;
                backdrop-filter: blur(12px);
                background: rgba(13,45,82,0.5);
            }
        }
    </style>
</head>
<body>

<svg class="bg-svg" viewBox="0 0 1000 600" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
    <rect width="1000" height="600" fill="#ffffff"/>
    <g id="circles-left">
        <circle cx="240" cy="320" r="185" fill="#184E77"/>
        <circle cx="240" cy="320" r="140" fill="#2B648F"/>
        <circle cx="365" cy="435" r="80"  fill="#5D91B5"/>
        <circle cx="398" cy="487" r="10"  fill="white"/>
        <circle cx="411" cy="477" r="7"   fill="white"/>
        <circle cx="421" cy="468" r="4"   fill="white"/>
        <circle cx="365" cy="175" r="60"  fill="#0D3F66"/>
        <circle cx="396" cy="205" r="7"   fill="white"/>
        <circle cx="406" cy="197" r="5"   fill="white"/>
        <circle cx="414" cy="190" r="3"   fill="white"/>
        <circle cx="105" cy="190" r="45"  fill="#4F7FA2"/>
        <circle cx="130" cy="217" r="5"   fill="white"/>
        <circle cx="138" cy="210" r="3.5" fill="white"/>
        <circle cx="145" cy="204" r="2"   fill="white"/>
        <image href="{{ asset('images/illustration.png') }}" x="25" y="130" width="420" height="420" preserveAspectRatio="xMidYMid meet"/>
    </g>
    <g id="circles-right">
        <circle cx="650" cy="80"  r="130" fill="#184E77"/>
        <circle cx="650" cy="300" r="100" fill="#184E77"/>
        <circle cx="650" cy="520" r="130" fill="#184E77"/>
        <circle cx="675" cy="80"  r="130" fill="#0d2d52"/>
        <circle cx="675" cy="300" r="100" fill="#0d2d52"/>
        <circle cx="675" cy="520" r="130" fill="#0d2d52"/>
        <rect x="675" y="0" width="325" height="600" fill="#0d2d52"/>
    </g>
    <rect id="mobile-bg-fill" x="800" y="0" width="200" height="600" fill="#0d2d52"/>
</svg>

<div class="login-wrapper">

    {{-- Logo --}}
    <div style="position:fixed;top:20px;left:20px;z-index:100;">
        <img src="{{ asset('images/logo.png') }}" alt="Brangkas Garage"
             style="width:64px;height:64px;border-radius:16px;object-fit:cover;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
    </div>

    <div class="left-panel"></div>

    <div class="right-panel">
        <div class="form-box">

            <h1 class="form-title">Lupa Password</h1>

            {{-- Step indicator --}}
            <div class="steps">
                <div class="step-dot {{ $step === 'send' ? 'active' : 'done' }}">1</div>
                <div class="step-line {{ in_array($step, ['verify','reset']) ? 'done' : '' }}"></div>
                <div class="step-dot {{ $step === 'verify' ? 'active' : ($step === 'reset' ? 'done' : '') }}">2</div>
                <div class="step-line {{ $step === 'reset' ? 'done' : '' }}"></div>
                <div class="step-dot {{ $step === 'reset' ? 'active' : '' }}">3</div>
            </div>

            {{-- ══════════════════════════════════════ --}}
            {{-- STEP 1: Input email                   --}}
            {{-- ══════════════════════════════════════ --}}
            @if($step === 'send')

                <p class="form-subtitle">Masukkan email akun Anda. Kami akan mengirimkan kode verifikasi 6 digit.</p>

                <form method="POST" action="{{ route('password.send-otp') }}">
                    @csrf

                    <div class="field-group">
                        <label class="field-label">Email</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   required autofocus placeholder="email@contoh.com"
                                   class="field-input">
                        </div>
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary"> Kirim Kode Verifikasi</button>
                </form>

                <a href="{{ route('login') }}" class="back-link">← Kembali ke Login</a>

            {{-- ══════════════════════════════════════ --}}
            {{-- STEP 2: Verifikasi OTP                --}}
            {{-- ══════════════════════════════════════ --}}
            @elseif($step === 'verify')

                <p class="form-subtitle">Kode verifikasi dikirim ke<br><strong style="color:#fff;">{{ $email }}</strong><br>Berlaku 10 menit. Cek inbox atau spam.</p>

                @if($errors->verifyForgot->has('otp_code'))
                    <div class="field-error" style="margin-bottom:0.8rem;font-size:0.75rem;">
                        ❌ {{ $errors->verifyForgot->first('otp_code') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.verify-otp') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="field-group">
                        <label class="field-label">Kode Verifikasi (6 digit)</label>
                        <div class="field-wrap">
                            <input type="text" name="otp_code" maxlength="6"
                                   placeholder="000000" autocomplete="off"
                                   value="{{ old('otp_code') }}"
                                   class="field-input otp-input">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary"> Verifikasi Kode</button>
                </form>

                <form method="POST" action="{{ route('password.send-otp') }}" style="margin-top:0.5rem;">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="resend-btn">Kirim ulang kode</button>
                </form>

                <a href="{{ route('password.request') }}" class="back-link">← Ganti email</a>

            {{-- ══════════════════════════════════════ --}}
            {{-- STEP 3: Input password baru           --}}
            {{-- ══════════════════════════════════════ --}}
            @elseif($step === 'reset')

                <p class="form-subtitle">Kode valid! Masukkan password baru untuk akun<br><strong style="color:#fff;">{{ $email }}</strong></p>

                @if($errors->resetForgot->any())
                    <div class="field-error" style="margin-bottom:0.8rem;font-size:0.75rem;">
                        ❌ {{ $errors->resetForgot->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset-otp') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="field-group">
                        <label class="field-label">Password Baru</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <input type="password" name="password" id="pw1"
                                   required autocomplete="new-password"
                                   placeholder="Min. 6 karakter"
                                   class="field-input pw-input">
                            <button type="button" onclick="togglePw('pw1','eye1')" class="toggle-pw">
                                <svg id="eye1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Konfirmasi Password Baru</label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <input type="password" name="password_confirmation" id="pw2"
                                   required autocomplete="new-password"
                                   placeholder="Ulangi password"
                                   class="field-input pw-input">
                            <button type="button" onclick="togglePw('pw2','eye2')" class="toggle-pw">
                                <svg id="eye2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary"> Simpan Password Baru</button>
                </form>

                <a href="{{ route('password.request') }}" class="back-link">← Mulai ulang</a>

            @endif

        </div>
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

</body>
</html>
