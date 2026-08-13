<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Kode Akses Orang Tua - SMAN 15 Padang</title>
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
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --ease-out: cubic-bezier(0.22, 0.61, 0.36, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            background: radial-gradient(circle at top, rgba(67, 160, 71, 0.08), transparent 36%), var(--bg-body);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-heading);
            letter-spacing: -0.02em;
        }

        .code-card {
            width: 100%;
            max-width: 560px;
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 60px rgba(27, 94, 32, 0.08);
            overflow: hidden;
            border: 1px solid rgba(27, 94, 32, 0.05);
        }

        .code-header {
            background: linear-gradient(145deg, #143d16 0%, var(--primary) 30%, var(--secondary) 80%, #66BB6A 100%);
            color: #fff;
            padding: 28px 30px;
        }

        .code-header h1 {
            color: #fff;
            font-size: 1.4rem;
            margin: 0 0 6px;
            font-weight: 800;
        }

        .code-header p {
            margin: 0;
            color: rgba(255,255,255,0.78);
            font-size: 0.95rem;
        }

        .code-body {
            padding: 28px 30px 30px;
        }

        .code-box {
            border: 1px dashed rgba(27, 94, 32, 0.18);
            background: rgba(67, 160, 71, 0.06);
            border-radius: var(--radius-md);
            padding: 18px 16px;
            text-align: center;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            color: var(--primary);
            word-break: break-all;
        }

        .meta-box {
            background: rgba(249, 168, 37, 0.08);
            border-left: 4px solid var(--accent);
            border-radius: var(--radius-sm);
            padding: 16px;
            font-size: 0.9rem;
            color: var(--text-body);
            line-height: 1.6;
        }

        .btn-primary-theme {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 12px 18px;
            border-radius: var(--radius-md);
            transition: all 0.3s var(--ease-spring);
            box-shadow: 0 4px 16px rgba(27, 94, 32, 0.15);
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary-theme:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 24px rgba(27, 94, 32, 0.22);
        }

        .btn-outline-theme {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(27, 94, 32, 0.18);
            color: var(--primary);
            background: white;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 12px 18px;
            border-radius: var(--radius-md);
            text-decoration: none;
        }

        .small-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
    </style>
</head>
<body>
    <div class="code-card">
        <div class="code-header">
            <div class="small-label mb-2" style="color: rgba(255,255,255,0.72);">Reveal Akses</div>
            <h1>Kode Akses Orang Tua</h1>
            <p>{{ $student->user?->name ?? 'Siswa' }}</p>
        </div>

        <div class="code-body">
            <div class="mb-3">
                <div class="small-label mb-2">Kode aktif</div>
                <div class="code-box">{{ $code }}</div>
            </div>

            <div class="meta-box mb-4">
                <i class="fas fa-shield-alt me-1" style="color: var(--accent);"></i>
                Halaman ini hanya ditampilkan setelah login staf. Setelah selesai, tutup tab ini agar kode tidak tetap terbuka.
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn-primary-theme flex-grow-1" id="copyCode" data-code="{{ $code }}">
                    <i class="fas fa-copy"></i> Salin Kode
                </button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('copyCode')?.addEventListener('click', function() {
            const code = this.getAttribute('data-code');
            navigator.clipboard.writeText(code).then(() => {
                this.innerHTML = '<i class="fas fa-check"></i> Tersalin';
            }).catch(err => {
                console.error('Gagal menyalin kode: ', err);
            });
        });
    </script>
</body>
</html>
