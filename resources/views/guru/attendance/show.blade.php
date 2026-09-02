@extends('layouts.lms')

@section('title', 'Detail Kehadiran')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div class="d-flex align-items-center gap-3">
            @php
                $backUrl = $attendance->meeting_id 
                    ? route('guru.meetings.show', $attendance->meeting_id) 
                    : route('guru.attendances.index', ['class_id' => $attendance->class_id, 'subject_id' => $attendance->subject_id]);
            @endphp
            <a class="btn btn-outline-secondary-theme btn-sm" href="{{ $backUrl }}" style="border-radius: var(--radius-sm);">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">🔍 Detail Kehadiran</h1>
                <p class="text-muted mb-0">Informasi lengkap kehadiran siswa pada sesi ini</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('guru.attendances.edit', $attendance->id) }}" class="btn btn-primary btn-sm" style="border-radius: var(--radius-sm);">
                <i class="fas fa-edit me-1"></i> Edit Absensi
            </a>
            <form action="{{ route('guru.attendances.destroy', $attendance->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh rekaman absensi ini?');" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: var(--radius-sm);">
                    <i class="fas fa-trash-alt me-1"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Info Card -->
        <div class="col-lg-4 mb-4 reveal reveal-delay-1">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Informasi Kelas</h5>
                </div>
                <div class="content-card-body">
                    <div class="mb-3">
                        <label class="text-muted small d-block fw-semibold mb-1">Tanggal & Waktu Presensi</label>
                        <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->date)->format('d F Y') }}</span>
                        @if($attendance->formatted_submitted_time)
                            <span class="badge bg-light text-primary border ms-2 font-monospace">
                                <i class="far fa-clock me-1"></i>Diisi jam {{ $attendance->formatted_submitted_time }} WIB
                            </span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small d-block fw-semibold mb-1">Kelas</label>
                        <span class="status-badge status-badge--hadir">{{ $attendance->schoolClass?->name }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small d-block fw-semibold mb-1">Mata Pelajaran</label>
                        <span class="fw-bold text-dark">{{ $attendance->subject?->name }}</span>
                    </div>

                    @if($attendance->meeting_id)
                    <div class="mb-3">
                        <label class="text-muted small d-block fw-semibold mb-1">Pertemuan</label>
                        <a href="{{ route('guru.meetings.show', $attendance->meeting_id) }}" class="fw-bold text-decoration-none d-flex align-items-center gap-1" style="color: var(--primary);">
                            <i class="fas fa-calendar-check"></i> Pertemuan Ke-{{ $attendance->meeting->number }}
                        </a>
                    </div>
                    @endif

                    @php
                        $stats = $attendance->details->groupBy('status')->map->count();
                    @endphp

                    <div class="mt-4 pt-3 border-top" style="border-color: rgba(37, 103, 30, 0.06) !important;">
                        <label class="text-muted small d-block fw-semibold mb-2">Ringkasan:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="status-badge status-badge--hadir">Hadir: {{ $stats['hadir'] ?? 0 }}</span>
                            <span class="status-badge status-badge--izin">Izin: {{ $stats['izin'] ?? 0 }}</span>
                            <span class="status-badge status-badge--sakit">Sakit: {{ $stats['sakit'] ?? 0 }}</span>
                            <span class="status-badge status-badge--alpa">Alpa: {{ $stats['alpa'] ?? 0 }}</span>
                            <span class="status-badge status-badge--cabut">Cabut: {{ $stats['cabut'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student List -->
        <div class="col-lg-8 mb-4 reveal reveal-delay-2">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Daftar Kehadiran Siswa</h5>
                </div>
                <div class="content-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700;">Nama Siswa</th>
                                    <th class="text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700;">NISN</th>
                                    <th class="text-center text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700; width: 140px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance->details as $detail)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $detail->student->user?->name ?? 'Siswa Tanpa Akun User' }}</div>
                                        </td>
                                        <td class="text-muted">{{ $detail->student->nisn }}</td>
                                        <td class="text-center">
                                            @php
                                                $badgeClass = match($detail->status) {
                                                    'hadir' => 'status-badge--hadir',
                                                    'izin' => 'status-badge--izin',
                                                    'sakit' => 'status-badge--sakit',
                                                    'alpa' => 'status-badge--alpa',
                                                    'cabut' => 'status-badge--cabut',
                                                    default => ''
                                                };
                                            @endphp
                                            <span class="status-badge {{ $badgeClass }} px-3 py-2 text-uppercase d-inline-block" style="min-width: 90px; text-align: center;">
                                                {{ $detail->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
