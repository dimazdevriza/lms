<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berkas Tidak Ditemukan - LMS SMA Negeri 15 Padang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/lms.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
            color: #1e293b;
        }
        .error-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
            padding: 40px 32px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 24px;
        }
        .path-box {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 12px 16px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8rem;
            color: #475569;
            word-break: break-all;
            margin: 20px 0;
            border: 1px dashed #cbd5e1;
            text-align: left;
        }
        .btn-group-action {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .btn-custom {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-primary-custom {
            background: var(--primary, #1e6b37);
            color: #ffffff;
        }
        .btn-primary-custom:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: #ffffff;
        }
        .btn-secondary-custom {
            background: #e2e8f0;
            color: #475569;
        }
        .btn-secondary-custom:hover {
            background: #cbd5e1;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="fas fa-file-circle-xmark"></i>
        </div>
        <h4 style="font-weight: 800; font-size: 1.35rem; margin-bottom: 12px; color: #0f172a;">
            Berkas Lampiran Tidak Ditemukan
        </h4>
        <p style="color: #64748b; font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px;">
            @if(!empty($ownerName))
                Berkas tugas milik <strong>{{ $ownerName }}</strong> tidak ditemukan dalam sistem penyimpanan server.
            @else
                Berkas yang Anda cari tidak ditemukan dalam sistem penyimpanan server.
            @endif
            Hal ini dapat terjadi apabila berkas terhapus saat pemeliharaan server, atau koneksi terputus saat proses pengunggahan.
        </p>

        @if(!empty($filePath))
            <div class="path-box">
                <div style="font-weight: 700; color: #334155; margin-bottom: 4px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-database me-1"></i> Database File Path:
                </div>
                <span>{{ $filePath }}</span>
            </div>
        @endif

        <div class="btn-group-action">
            <button onclick="window.close()" class="btn-custom btn-secondary-custom">
                <i class="fas fa-xmark"></i> Tutup Tab Ini
            </button>
            <button onclick="history.back()" class="btn-custom btn-primary-custom">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
        </div>
    </div>
</body>
</html>
