@extends('layouts.lms')

@section('title', 'Absensi Kelas - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">📋 Absensi Harian Kelas {{ $class->name }}</h1>
                    <p>Kelola absensi siswa perwalian Anda secara berkala</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('guru.classroom.index') }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('guru.classroom.attendance.create', $class) }}" class="btn btn-light d-inline-flex align-items-center gap-2" style="color: var(--primary) !important; font-weight: 700;">
                        <i class="fas fa-plus"></i> Input Absensi Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm reveal" role="alert" style="border-radius: var(--radius-md);">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Section 1: Akumulasi Kehadiran Per Siswa (Semua Mapel & Harian) -->
    <div class="content-card reveal mb-4">
        <div class="content-card-header bg-white py-3">
            <div class="content-card-header-icon" style="background-color: rgba(13, 110, 253, 0.08); color: #0d6efd;">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div>
                <h5 class="content-card-title mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;">Akumulasi Kehadiran Siswa Perwalian</h5>
                <small class="text-muted">Rekapitulasi akumulasi gabungan presensi seluruh mata pelajaran & presensi harian</small>
            </div>
        </div>
        <div class="content-card-body p-0">
            @if(count($students) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 50px;">No.</th>
                                <th>👤 Nama Siswa</th>
                                <th class="text-center">✓ Hadir</th>
                                <th class="text-center">📋 Izin</th>
                                <th class="text-center">🏥 Sakit</th>
                                <th class="text-center">❌ Alpa</th>
                                <th class="text-center" style="width: 180px;">Persentase</th>
                                <th class="text-center" style="width: 130px;">Rincian Mapel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $idx => $student)
                                @php
                                    $acc = $accumulatedAttendance[$student->id] ?? [
                                        'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0, 'percentage' => 100, 'subject_breakdown' => []
                                    ];
                                    $pct = $acc['percentage'];
                                    $badgeBg = $pct >= 90 ? 'bg-success' : ($pct >= 75 ? 'bg-warning text-dark' : 'bg-danger');
                                @endphp
                                <tr>
                                    <td class="ps-4 text-muted fw-semibold">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $student->user->name }}</div>
                                        <div class="text-muted small">NISN: {{ $student->nisn }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-2 py-1" style="background-color: rgba(67, 160, 71, 0.12); color: #2E7D32;">
                                            {{ $acc['hadir'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-2 py-1" style="background-color: rgba(249, 168, 37, 0.12); color: #B26A00;">
                                            {{ $acc['izin'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-2 py-1" style="background-color: rgba(255, 152, 0, 0.12); color: #E65100;">
                                            {{ $acc['sakit'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-2 py-1" style="background-color: rgba(198, 40, 40, 0.10); color: #C62828;">
                                            {{ $acc['alpa'] }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                                                <div class="progress-bar {{ $badgeBg }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="fw-bold small {{ $pct >= 90 ? 'text-success' : ($pct >= 75 ? 'text-warning' : 'text-danger') }}">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAttendanceSubject{{ $student->id }}">
                                            <i class="fas fa-list-check me-1"></i> Mapel
                                        </button>

                                        <!-- Modal Rincian Presensi Mapel -->
                                        <div class="modal fade text-start" id="modalAttendanceSubject{{ $student->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light">
                                                        <h6 class="modal-header-title fw-bold mb-0">
                                                            <i class="fas fa-clipboard-user text-primary me-2"></i> Presensi Per Mata Pelajaran - {{ $student->user->name }}
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-0">
                                                        @if(count($acc['subject_breakdown']) > 0)
                                                            <div class="table-responsive">
                                                                <table class="table table-hover align-middle mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th class="ps-4">Mata Pelajaran</th>
                                                                            <th class="text-center">✓ Hadir</th>
                                                                            <th class="text-center">📋 Izin</th>
                                                                            <th class="text-center">🏥 Sakit</th>
                                                                            <th class="text-center">❌ Alpa</th>
                                                                            <th class="text-center pe-4">Kehadiran</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($acc['subject_breakdown'] as $subjName => $sData)
                                                                            <tr>
                                                                                <td class="ps-4 fw-bold text-dark">{{ $subjName }}</td>
                                                                                <td class="text-center"><span class="badge bg-success-subtle text-success fw-bold px-2 py-1">{{ $sData['hadir'] }}</span></td>
                                                                                <td class="text-center"><span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1">{{ $sData['izin'] }}</span></td>
                                                                                <td class="text-center"><span class="badge bg-info-subtle text-info fw-bold px-2 py-1">{{ $sData['sakit'] }}</span></td>
                                                                                <td class="text-center"><span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">{{ $sData['alpa'] }}</span></td>
                                                                                <td class="text-center pe-4 fw-bold text-primary">{{ $sData['percentage'] }}%</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="p-4 text-center text-muted">Belum ada catatan presensi mata pelajaran dari guru bidang studi.</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Daftar Absensi Harian -->
    <div class="content-card reveal reveal-delay-1">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <i class="fas fa-history"></i>
            </div>
            <h5 class="content-card-title">Riwayat Absensi Kehadiran</h5>
        </div>
        <div class="content-card-body">
            @if($attendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>📅 Tanggal</th>
                                <th class="text-center">✓ Hadir</th>
                                <th class="text-center">📋 Izin</th>
                                <th class="text-center">🏥 Sakit</th>
                                <th class="text-center">❌ Alpa</th>
                                <th class="text-center" style="width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendances as $attendance)
                                @php
                                    $hadir = $attendance->details->whereIn('status', ['hadir'])->count();
                                    $izin = $attendance->details->where('status', 'izin')->count();
                                    $sakit = $attendance->details->where('status', 'sakit')->count();
                                    $alpa = $attendance->details->where('status', 'alpa')->count();
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $attendance->date->translatedFormat('d F Y') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-3 py-2" style="background-color: rgba(67, 160, 71, 0.12); color: #2E7D32;">
                                            {{ $hadir }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-3 py-2" style="background-color: rgba(249, 168, 37, 0.12); color: #B26A00;">
                                            {{ $izin }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-3 py-2" style="background-color: rgba(255, 152, 0, 0.12); color: #E65100;">
                                            {{ $sakit }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill fw-bold px-3 py-2" style="background-color: rgba(198, 40, 40, 0.10); color: #C62828;">
                                            {{ $alpa }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('guru.classroom.attendance.show', [$class, $attendance]) }}" class="btn btn-sm btn-outline-primary-theme px-3">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $attendances->links() }}
                </div>
            @else
                <div class="empty-state py-5">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h5 class="empty-state-text mt-3">Belum Ada Data Absensi</h5>
                    <p class="text-muted">Mulai catat kehadiran harian kelas untuk memantau keaktifan siswa.</p>
                    <a href="{{ route('guru.classroom.attendance.create', $class) }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Input Absensi Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
