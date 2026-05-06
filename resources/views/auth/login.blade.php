<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name', 'Berangkas Garage') }}</title>
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

        /* Wrapper full screen */
        .login-wrapper {
            width: 100%;
            min-height: 100vh;
            position: relative;
            display: flex;
        }

        /* SVG background penuh — menggambar semua elemen visual */
        .bg-svg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
        }

        /* LEFT PANEL — area kiri, transparan di atas SVG */
        .left-panel {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        /* RIGHT PANEL — area kanan, form login */
        .right-panel {
            width: 38%;
            min-width: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 3rem;
            position: relative;
            z-index: 1;
        }

        .form-box { width: 100%; max-width: 320px; }

        .form-title {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }

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

        .field-error { margin-top: 0.22rem; font-size: 0.7rem; color: #f87171; }

        .btn-login {
            display: block;
            margin: 1.4rem auto 0;
            padding: 0.52rem 2.2rem;
            border-radius: 0.38rem;
            border: none;
            background: #1e5fa8;
            color: #fff;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.85; }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 0.9rem;
            font-size: 0.7rem;
            color: #7bafd4;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        .status-msg {
            margin-bottom: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #7dd3fc;
            text-align: center;
        }

        /* Toggle password button */
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

        @media (max-width: 680px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; }
            /* Sembunyikan lingkaran kiri di mobile */
            #circles-left { display: none; }
            /* Geser lingkaran kanan ke kiri, sisakan jarak dari tepi kiri */
            #circles-right { transform: translateX(-170px); }
            /* Efek stroke/border pada field input agar terlihat di mobile */
            .field-input {
                border: 1.5px solid rgba(255,255,255,0.4) !important;
            }
            .field-input:focus {
                border-color: rgba(255,255,255,0.8) !important;
                box-shadow: 0 0 0 2px rgba(255,255,255,0.15) !important;
            }
            /* Stroke pada form box keseluruhan */
            .form-box {
                border: 1.5px solid rgba(255,255,255,0.2);
                border-radius: 1.5rem;
                padding: 1.5rem;
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                background: rgba(13,45,82,0.5);
                box-shadow: 0 0 0 1px rgba(255,255,255,0.08), 0 8px 32px rgba(0,0,0,0.3);
            }
        }
    </style>
</head>
<body>

{{--
    SVG BACKGROUND PENUH
    ====================
    viewBox: 0 0 1000 600  (landscape, proporsional layar)

    KIRI (0–500): background putih + lingkaran-lingkaran
    KANAN (500–1000): background biru gelap + gelombang sebagai separator
--}}
<svg class="bg-svg"
     viewBox="0 0 1000 600"
     preserveAspectRatio="xMidYMid slice"
     xmlns="http://www.w3.org/2000/svg">

    <!-- Background putih (kiri) -->
    <rect width="1000" height="600" fill="#ffffff"/>

    <!-- ===== LINGKARAN KIRI (desktop only, disembunyikan di mobile via CSS) ===== -->
    <g id="circles-left">
        <!-- Outer circle -->
        <circle cx="240" cy="320" r="185" fill="#184E77"/>
        <!-- Inner circle -->
        <circle cx="240" cy="320" r="140" fill="#2B648F"/>

        <!-- Bubble kanan bawah (no.3) -->
        <circle cx="365" cy="435" r="80"  fill="#5D91B5"/>
        <circle cx="398" cy="487" r="10"  fill="white"/>
        <circle cx="411" cy="477" r="7"   fill="white"/>
        <circle cx="421" cy="468" r="4"   fill="white"/>

        <!-- Bubble kanan atas (no.4) -->
        <circle cx="365" cy="175" r="60"  fill="#0D3F66"/>
        <circle cx="396" cy="205" r="7"   fill="white"/>
        <circle cx="406" cy="197" r="5"   fill="white"/>
        <circle cx="414" cy="190" r="3"   fill="white"/>

        <!-- Bubble kiri atas (no.5) -->
        <circle cx="105" cy="190" r="45"  fill="#4F7FA2"/>
        <circle cx="130" cy="217" r="5"   fill="white"/>
        <circle cx="138" cy="210" r="3.5" fill="white"/>
        <circle cx="145" cy="204" r="2"   fill="white"/>

        <!-- Gambar ilustrasi di tengah lingkaran -->
        <image href="{{ asset('images/illustration.png') }}"
               x="25" y="130"
               width="420" height="420"
               preserveAspectRatio="xMidYMid meet"/>
    </g>

    <!-- Set 1: layer belakang (biru medium) -->
    <g id="circles-right">
        <circle cx="650" cy="80"  r="130" fill="#184E77"/>
        <circle cx="650" cy="300" r="100" fill="#184E77"/>
        <circle cx="650" cy="520" r="130" fill="#184E77"/>

        <!-- Set 2: layer depan (biru gelap) -->
        <circle cx="675" cy="80"  r="130" fill="#0d2d52"/>
        <circle cx="675" cy="300" r="100" fill="#0d2d52"/>
        <circle cx="675" cy="520" r="130" fill="#0d2d52"/>

        <!-- Persegi panjang kanan: background biru gelap penuh -->
        <rect x="675" y="0" width="325" height="600" fill="#0d2d52"/>
    </g>

    <!-- Rect tambahan untuk mobile — menutupi celah putih di kanan saat digeser -->
    <rect id="mobile-bg-fill" x="800" y="0" width="200" height="600" fill="#0d2d52"/>

</svg>

<div class="login-wrapper">

    {{-- Logo pojok kiri atas --}}
    <div style="position:fixed;top:20px;left:20px;z-index:100;">
        <img src="{{ asset('images/logo.png') }}"
             alt="Brangkas Garage"
             style="width:64px;height:64px;border-radius:16px;object-fit:cover;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
    </div>

    <div class="left-panel"></div>

    <div class="right-panel">
        <div class="form-box">

            @if (session('status'))
                <p class="status-msg">{{ session('status') }}</p>
            @endif

            <h1 class="form-title">Login</h1>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field-group">
                    <label for="email" class="field-label">Username</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               required autofocus autocomplete="username"
                               placeholder="Masukan Username di" class="field-input"/>
                    </div>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="field-group">
                    <label for="password" class="field-label">Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <input id="password" type="password" name="password"
                               required autocomplete="current-password"
                               placeholder="Masukan Password" class="field-input" style="padding-right:2.5rem;"/>
                        <!-- Toggle show/hide password -->
                        <button type="button" onclick="togglePassword()" class="toggle-pw" title="Tampilkan/sembunyikan password">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-login">LOGIN</button>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
                @endif
            </form>

            {{-- Divider --}}
            <div style="display:flex;align-items:center;margin:1.2rem 0 1rem;">
                <div style="flex:1;height:1px;background:rgba(255,255,255,0.15);"></div>
                <span style="margin:0 0.7rem;font-size:0.7rem;color:#7bafd4;">atau</span>
                <div style="flex:1;height:1px;background:rgba(255,255,255,0.15);"></div>
            </div>

            {{-- Tombol Login Google --}}
            <a href="{{ route('auth.google') }}"
               style="display:flex;align-items:center;justify-content:center;gap:0.6rem;
                      width:100%;padding:0.55rem 1rem;border-radius:0.38rem;
                      background:#ffffff;color:#1a3a5c;font-size:0.78rem;font-weight:600;
                      text-decoration:none;transition:opacity 0.2s;"
               onmouseover="this.style.opacity='0.85'"
               onmouseout="this.style.opacity='1'">
                <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Login dengan Google
            </a>

        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eye-icon');
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';

        // Ganti ikon: mata terbuka ↔ mata tertutup
        icon.innerHTML = isHidden
            ? /* mata tertutup */
              '<path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 00-1.06 1.06l14.5 14.5a.75.75 0 101.06-1.06l-1.745-1.745a10.029 10.029 0 003.3-4.38 1.651 1.651 0 000-1.185A10.004 10.004 0 009.999 3a9.956 9.956 0 00-4.744 1.194L3.28 2.22zM7.752 6.69l1.092 1.092a2.5 2.5 0 013.374 3.373l1.091 1.092a4 4 0 00-5.557-5.557z" clip-rule="evenodd"/><path d="M10.748 13.93l2.523 2.523a9.987 9.987 0 01-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 010-1.186A10.007 10.007 0 012.839 6.02L6.07 9.252a4 4 0 004.678 4.678z"/>'
            : /* mata terbuka */
              '<path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>';
    }
</script>

</body>
</html>
