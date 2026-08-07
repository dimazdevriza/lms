<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LMS SMA Negeri 15 Padang') }} - Login</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Guest CSS stylesheet -->
        <link rel="stylesheet" href="{{ asset('css/guest.css') }}?v={{ time() }}">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        @stack('styles')
    </head>
    <body>
        <!-- Ambient Background Orbs -->
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>
        <div class="ambient-orb orb-3"></div>

        <div class="login-container">
            <div class="login-card">
                <div class="logo-wrapper">
                    <a href="/">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Logo SMA Negeri 15 Padang" />
                    </a>
                    <h5 class="school-brand-title">LMS SMAN 15 Padang</h5>
                </div>

                <div class="form-box">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer credit -->
            <p class="footer-credit">
                &copy; {{ date('Y') }} LMS SMA Negeri 15 Padang · Hak Cipta Dilindungi
            </p>
        </div>

        <script>
            function togglePasswordVisibility(inputId, button) {
                const input = document.getElementById(inputId);
                if (!input) return;
                const icon = button.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) icon.className = 'far fa-eye-slash';
                } else {
                    input.type = 'password';
                    if (icon) icon.className = 'far fa-eye';
                }
            }
        </script>
        @stack('scripts')
    </body>
</html>
