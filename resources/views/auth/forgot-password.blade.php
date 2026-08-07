<x-guest-layout>
    <!-- Page Title -->
    <div class="page-heading">
        <h2>{{ __('Reset Password') }}</h2>
        <p>{{ __('Lupa password? Masukkan email Anda atau hubungi Admin via WhatsApp.') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

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
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="Masukkan email terdaftar Anda"
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

        <button type="submit" class="btn-login mt-3">
            <i class="fas fa-paper-plane me-2"></i> {{ __('Kirim Link Reset Password') }}
        </button>
    </form>

    <!-- Alternative Reset via WhatsApp Helpdesk -->
    <div style="background: rgba(249, 168, 37, 0.08); border: 1px dashed rgba(249, 168, 37, 0.4); border-radius: var(--radius-md); padding: 1rem; margin-top: 1.5rem; text-align: left;">
        <div style="font-weight: 700; font-size: 0.8rem; color: #B26A00; margin-bottom: 0.35rem; display: flex; align-items: center;">
            <i class="fas fa-info-circle me-1.5" style="font-size: 0.9rem;"></i> Tidak Memiliki Akses Email Sekolah?
        </div>
        <p style="font-size: 0.775rem; color: #475569; margin-bottom: 0.75rem; line-height: 1.5;">
            Jika Anda tidak memiliki akses ke email terdaftar, Anda dapat langsung meminta bantuan reset password ke Admin Sekolah melalui WhatsApp:
        </p>

        <a href="https://wa.me/6281292321071?text=Halo%20Admin%20LMS%20SMA%20Negeri%2015%20Padang,%20saya%20lupa%20password%20akun%20LMS%20saya.%20Mohon%20bantuan%20reset%20password." target="_blank" class="btn-whatsapp-help">
            <i class="fab fa-whatsapp me-2" style="font-size: 1.1rem;"></i> Chat Admin via WhatsApp
        </a>
    </div>

    <div class="parent-access-divider">
        <a href="{{ route('login') }}" class="forgot-link">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Login
        </a>
    </div>
</x-guest-layout>
