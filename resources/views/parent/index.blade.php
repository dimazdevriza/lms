<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemantauan Orang Tua - SMAN 15 Padang</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/v4-shims.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1B5E20;
            --primary-light: #2E7D32;
            --secondary: #43A047;
            --accent: #F9A825;
            --bg-body: #FAFAF7;
            --bg-card: #FFFFFF;
            --text-heading: #1A1A1A;
            --text-body: #4A4A4A;
            --text-muted: #888888;
            --shadow-warm: 0 4px 24px rgba(37, 103, 30, 0.06);
            --shadow-warm-hover: 0 8px 32px rgba(37, 103, 30, 0.10);
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --ease-out: cubic-bezier(0.22, 0.61, 0.36, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }

        /* ── Animations ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* ── Card Layout ── */
        .access-card {
            background: var(--bg-card);
            border: 1px solid rgba(37, 103, 30, 0.04);
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 60px rgba(27, 94, 32, 0.08);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            margin: 2rem 1rem;
            animation: fadeInUp 0.6s var(--ease-out) both;
        }

        /* ── Header Banner ── */
        .card-header-banner {
            background: linear-gradient(145deg, #143d16 0%, var(--primary) 30%, var(--secondary) 80%, #66BB6A 100%);
            color: white;
            padding: 40px 32px 48px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.04) 0%, transparent 40%);
            pointer-events: none;
        }

        .card-header-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .header-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
        }

        .header-wave svg {
            display: block;
            width: 100%;
            height: 24px;
        }

        .logo-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.25);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            position: relative;
            z-index: 2;
        }

        .card-header-banner h4 {
            font-weight: 800;
            font-size: 1.25rem;
            margin: 0 0 4px;
            position: relative;
            z-index: 2;
        }

        .card-header-banner .subtitle {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.55);
            margin: 0;
            position: relative;
            z-index: 2;
        }

        /* ── Card Body ── */
        .card-body-inner {
            padding: 28px 32px 32px;
        }

        .card-body-inner .description {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .card-body-inner .description strong {
            color: var(--text-heading);
            font-weight: 600;
        }

        /* ── Form Controls ── */
        .form-control-access {
            border: 2px solid #E2E8F0;
            border-radius: var(--radius-md);
            padding: 14px 16px;
            font-size: 1.1rem;
            text-align: center;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--primary);
            transition: all 0.25s var(--ease-out);
            font-family: 'Plus Jakarta Sans', sans-serif;
            width: 100%;
        }

        .form-control-access::placeholder {
            color: #B0BEC5;
            letter-spacing: 1px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control-access:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 4px rgba(67, 160, 71, 0.12);
        }

        /* ── Button ── */
        .btn-access {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.9rem;
            padding: 14px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s var(--ease-spring);
            box-shadow: 0 4px 16px rgba(27, 94, 32, 0.15);
            gap: 0.5rem;
        }

        .btn-access:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--secondary));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(27, 94, 32, 0.22);
        }

        .btn-access:active {
            transform: translateY(0) scale(0.98);
        }

        /* ── Alerts ── */
        .alert {
            border: none;
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .alert-danger {
            background-color: #FFEBEE;
            color: #C62828;
            border: 1px solid rgba(198, 40, 40, 0.1);
        }

        .alert-success {
            background-color: #E8F5E9;
            color: var(--primary);
            border: 1px solid rgba(27, 94, 32, 0.1);
        }

        .rate-limit-warning {
            background: var(--bg-card);
            border-left: 4px solid var(--accent);
            border-radius: var(--radius-sm);
            padding: 14px 16px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: var(--text-body);
            box-shadow: 0 2px 8px rgba(249, 168, 37, 0.08);
        }

        /* ── Info Box ── */
        .info-box {
            background: rgba(249, 168, 37, 0.06);
            border-left: 4px solid var(--accent);
            border-radius: var(--radius-sm);
            padding: 16px;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-body);
            line-height: 1.6;
        }

        .info-box strong {
            color: var(--text-heading);
        }

        /* ── Back Link ── */
        .back-link {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: color 0.2s var(--ease-out);
        }

        .back-link:hover {
            color: var(--primary);
        }

        /* ── Footer ── */
        .page-footer {
            margin-top: auto;
            padding: 1.5rem;
            text-align: center;
        }

        .page-footer p {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 500;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="access-card">
        <div class="card-header-banner">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo SMA 15 Padang" class="logo-circle mb-3">
            <h4>Pemantauan Orang Tua</h4>
            <p class="subtitle">LMS SMA Negeri 15 Padang</p>
            <div class="header-wave">
                <svg viewBox="0 0 1440 40" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 40h1440V16c-200 16-400 24-720 20S200 8 0 24v16z" fill="#FFFFFF"/>
                </svg>
            </div>
        </div>

        <div class="card-body-inner">
            <p class="description">
                Masukkan <strong>Kode Akses Orang Tua</strong> yang diberikan oleh wali kelas untuk memantau perkembangan belajar putra/putri Anda.
            </p>

            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0" style="padding-left: 1rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success mb-3 text-center">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Rate Limit Warning (shown when Laravel returns 429) --}}
            @if(session('status') === 'throttled' || $errors->has('throttle'))
                <div class="rate-limit-warning">
                    <i class="fas fa-shield-alt me-1"></i>
                    <strong>Terlalu banyak percobaan.</strong> Silakan tunggu 1 menit sebelum mencoba lagi.
                </div>
            @endif

            <form action="{{ route('parent.access') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="text" 
                           class="form-control-access" 
                           name="parent_code" 
                           placeholder="Masukkan 6 Digit Kode Akses (Contoh: X8R2KP)" 
                           required 
                           autocomplete="off"
                           maxlength="15">
                </div>
                <button type="submit" class="btn-access mb-3">
                    <i class="fas fa-search"></i> Pantau Aktifitas Siswa
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ url('/') }}" class="back-link">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Utama Login
                </a>
            </div>

            <div class="info-box">
                <strong><i class="fas fa-info-circle me-1"></i> Informasi:</strong><br>
                Setiap siswa memiliki kode akses pemantauan unik yang terhubung langsung dengan databasenya (Nilai Tugas, Kehadiran Kelas, & Catatan Perilaku). Jika Anda tidak tahu kodenya, silakan hubungi Wali Kelas.
            </div>
        </div>
    </div>

    <div class="page-footer">
        <p>&copy; {{ date('Y') }} LMS SMA Negeri 15 Padang</p>
    </div>

</body>
</html>
