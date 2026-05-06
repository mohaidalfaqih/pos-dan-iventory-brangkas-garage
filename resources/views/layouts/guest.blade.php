<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Berangkas Garage') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="mx-auto h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold">
                    BG
                </div>
                <h1 class="mt-3 text-xl font-semibold text-slate-800">Brangkas Garage</h1>
                <p class="text-sm text-slate-500">Inventori & POS Suku Cadang Motor</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                {{ $slot }}
            </div>

            <div class="mt-4 text-center text-xs text-slate-500">
                © {{ date('Y') }} Brangkas Garage
            </div>
        </div>
    </div>
</body>
</html>
