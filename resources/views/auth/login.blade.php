<x-guest-layout>
    <!-- Page Title -->
    <div class="page-heading">
        <h2>{{ __('Selamat Datang') }}</h2>
        <p>{{ __('Silakan masuk ke portal LMS Anda') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">{{ __('Email / ID Pengguna') }}</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-envelope input-icon"></i>
                <input 
                    id="email" 
                    class="form-input has-icon" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    autocomplete="username"
                    placeholder="Masukkan email Anda"
                />
            </div>
            @if ($errors->has('email'))
                <div class="form-error">
                    @foreach ($errors->get('email') as $message)
                        <p><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input 
                    id="password" 
                    class="form-input has-icon"
                    type="password"
                    name="password"
                    required 
                    autocomplete="current-password"
                    placeholder="Masukkan password Anda"
                />
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', this)">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            @if ($errors->has('password'))
                <div class="form-error">
                    @foreach ($errors->get('password') as $message)
                        <p><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="form-options">
            <label for="remember_me" class="remember-checkbox">
                <input id="remember_me" type="checkbox" name="remember">
                <span class="remember-label">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="forgot-link" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <!-- Main Submit Button -->
        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt me-2"></i> {{ __('Masuk ke Akun') }}
        </button>
    </form>

    <!-- Parent Access Button Section -->
    <div class="parent-access-divider">
        <p class="parent-access-label">Apakah Anda Orang Tua Siswa?</p>
        <a href="{{ route('parent.index') }}" class="btn-parent-access">
            <i class="fas fa-user-shield me-2"></i> Pantau Aktivitas Anak (Orang Tua)
        </a>
    </div>
</x-guest-layout>
