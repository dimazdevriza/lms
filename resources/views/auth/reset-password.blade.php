<x-guest-layout>
    <!-- Page Title -->
    <div class="page-heading">
        <h2>{{ __('Reset Password') }}</h2>
        <p>{{ __('Buat password baru yang kuat untuk akun Anda') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">{{ __('Email Akun Anda') }}</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-envelope input-icon"></i>
                <input 
                    id="email" 
                    class="form-input has-icon" 
                    type="email" 
                    name="email" 
                    value="{{ old('email', $request->email) }}" 
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
            <label for="password" class="form-label">{{ __('Password Baru') }}</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input 
                    id="password" 
                    class="form-input has-icon"
                    type="password"
                    name="password"
                    required 
                    autocomplete="new-password"
                    placeholder="Masukkan password baru"
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
            <x-password-strength-meter inputId="password" confirmInputId="password_confirmation" />
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">{{ __('Konfirmasi Password Baru') }}</label>
            <div class="input-icon-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input 
                    id="password_confirmation" 
                    class="form-input has-icon"
                    type="password"
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password"
                    placeholder="Ketik ulang password baru Anda"
                />
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            @if ($errors->has('password_confirmation'))
                <div class="form-error">
                    @foreach ($errors->get('password_confirmation') as $message)
                        <p><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        <button type="submit" class="btn-login mt-3">
            <i class="fas fa-key me-2"></i> {{ __('Simpan Password Baru') }}
        </button>
    </form>
</x-guest-layout>
