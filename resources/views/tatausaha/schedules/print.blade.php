<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Jadwal Pelajaran {{ $academicYear ? '- ' . $academicYear->name : '' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1B5E20;
            --primary-light: #2E7D32;
            --secondary: #43A047;
            --accent: #F9A825;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            background: #fff;
            color: #1a1a1a;
            font-size: 10pt;
            line-height: 1.4;
        }

        .print-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 25px;
        }

        /* Header Sekolah */
        .school-header {
            text-align: center;
            border-bottom: 3px double var(--primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .school-header h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16pt;
            color: var(--primary);
            margin-bottom: 2px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .school-header h3 {
            font-size: 11pt;
            color: #444;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .school-header .sub-info {
            font-size: 9pt;
            color: #888;
        }

        /* Judul */
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14pt;
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }

        .doc-title .academic-year {
            font-size: 10pt;
            color: #666;
            font-weight: 500;
        }

        /* Class Section */
        .class-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .class-title {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 8px 16px;
            border-radius: 6px 6px 0 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11pt;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .class-title .major-badge {
            background: rgba(255,255,255,0.25);
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 600;
        }

        /* Schedule Grid */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ccc;
            font-size: 9pt;
        }

        .schedule-table th {
            background: #f0f0f0;
            color: #333;
            font-weight: 700;
            padding: 6px 8px;
            border: 1px solid #ccc;
            text-align: center;
            font-size: 8.5pt;
        }

        .schedule-table td {
            padding: 5px 6px;
            border: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
        }

        .schedule-table .time-col {
            background: #f8fafc;
            font-weight: 700;
            width: 100px;
            font-size: 8.5pt;
            color: #0f172a;
        }

        .schedule-table .break-row td {
            background: #fffbe6;
            color: #b8860b;
            font-style: italic;
            font-size: 8pt;
        }

        .schedule-table .subject-name {
            font-weight: 600;
            color: var(--primary);
            font-size: 8.5pt;
        }

        .schedule-table .teacher-name {
            font-size: 7.5pt;
            color: #888;
        }

        /* Footer & TTD */
        .print-footer {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
        }

        .ttd-section {
            text-align: center;
            min-width: 250px;
        }

        .ttd-section .place-date {
            font-size: 10pt;
            margin-bottom: 4px;
        }

        .ttd-section .role {
            font-size: 10pt;
            font-weight: 600;
            margin-bottom: 60px;
        }

        .ttd-section .name-line {
            border-top: 1px solid #333;
            display: inline-block;
            padding-top: 4px;
            min-width: 180px;
            font-size: 10pt;
            font-weight: 600;
        }

        .ttd-section .nip {
            font-size: 9pt;
            color: #666;
        }

        /* Toolbar (non-print) */
        .print-toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .print-toolbar .toolbar-title {
            color: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            font-size: 14px;
        }

        .print-toolbar .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .print-toolbar button, .print-toolbar a {
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }

        .btn-print {
            background: white;
            color: var(--primary);
        }

        .btn-print:hover {
            background: #f0f0f0;
            transform: translateY(-1px);
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3) !important;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.3);
        }

        .toolbar-spacer {
            height: 60px;
        }

        /* Print */
        @media print {
            .print-toolbar,
            .toolbar-spacer {
                display: none !important;
            }

            body {
                font-size: 9pt;
            }

            .print-container {
                padding: 0;
                max-width: 100%;
            }

            .class-section {
                page-break-inside: avoid;
            }

            .class-title {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }

            .schedule-table th,
            .schedule-table .time-col,
            .schedule-table .break-row td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Toolbar -->
    <div class="print-toolbar">
        <span class="toolbar-title">
            📅 Preview Cetak — Jadwal Pelajaran {{ $academicYear ? '(' . $academicYear->name . ')' : '' }}
        </span>
        <div class="toolbar-actions">
            <a href="{{ route('tatausaha.schedules.index') }}" class="btn-back">
                ← Kembali
            </a>
            <button onclick="window.print()" class="btn-print">
                🖨️ Cetak / Save PDF
            </button>
        </div>
    </div>
    <div class="toolbar-spacer"></div>

    <div class="print-container">
        <!-- Header -->
        <div class="school-header">
            <h3>PEMERINTAH PROVINSI SUMATERA BARAT</h3>
            <h3>DINAS PENDIDIKAN</h3>
            <h2>SMA NEGERI 15 PADANG</h2>
            <div class="sub-info">Jl. Limau Manis, Pauh, Kota Padang, Sumatera Barat</div>
        </div>

        <div class="doc-title">
            <h1>Jadwal Pelajaran Mingguan</h1>
            @if($academicYear)
                <div class="academic-year">Tahun Pelajaran: {{ $academicYear->name }}</div>
            @endif
        </div>

        <!-- Per Kelas -->
        @forelse($classes as $class)
            @php
                $classSchedules = $allSchedules[$class->id] ?? collect();
                $scheduleMap = $classSchedules->groupBy(fn($s) => $s->day . '_' . $s->time_slot_id);
            @endphp

            @if($classSchedules->isNotEmpty())
                <div class="class-section">
                    <div class="class-title">
                        Kelas {{ $class->name }}
                        @if($class->major)
                            <span class="major-badge">{{ $class->major }}</span>
                        @endif
                    </div>
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Waktu</th>
                                @foreach($activeDays as $day)
                                    <th>{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                                <tr class="{{ $slot->isBreak() ? 'break-row' : '' }}">
                                    <td class="time-col">
                                        {{ $slot->label }}<br>
                                        <span style="font-size: 7pt; color: #999;">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                        </span>
                                    </td>
                                    @foreach($activeDays as $day)
                                        <td>
                                            @if($slot->isBreak())
                                                <em>{{ $slot->label }}</em>
                                            @else
                                                @php
                                                    $entry = $scheduleMap[$day . '_' . $slot->id] ?? collect();
                                                    $schedule = $entry->first();
                                                @endphp
                                                @if($schedule && $schedule->subject)
                                                    <div class="subject-name">{{ $schedule->subject->name }}</div>
                                                    @if($schedule->teacher && $schedule->teacher->user)
                                                        <div class="teacher-name">{{ $schedule->teacher->user->name }}</div>
                                                    @endif
                                                @elseif($schedule && $schedule->activity)
                                                    <div class="subject-name">{{ $schedule->activity }}</div>
                                                @else
                                                    <span style="color: #ddd;">—</span>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <div style="text-align: center; padding: 40px; color: #999;">
                <p>Tidak ada data jadwal untuk ditampilkan.</p>
            </div>
        @endforelse

        <!-- TTD -->
        <div class="print-footer">
            <div class="ttd-section">
                <div class="place-date">Padang, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="role">Kepala Tata Usaha</div>
                <div class="name-line">&nbsp;</div>
                <div class="nip">NIP. ___________________</div>
            </div>
        </div>
    </div>
</body>
</html>
