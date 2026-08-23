@extends('layouts.lms')

@section('title', 'Input Absensi - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">🖊️ Input Absensi - {{ $class->name }}</h1>
                    <p>Catat absensi harian kelas perwalian Anda</p>
                </div>
                <a href="{{ route('guru.classroom.attendance', $class) }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.classroom.attendance.store', $class) }}">
        @csrf

        <div class="row">
            <div class="col-lg-4">
                <div class="content-card reveal reveal-delay-1">
                    <div class="content-card-header">
                        <div class="content-card-header-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5 class="content-card-title">Konfigurasi</h5>
                    </div>
                    <div class="content-card-body">
                        <!-- Tanggal Absensi -->
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 600;">📅 Tanggal</label>
                            <input class="form-control" type="date" name="date" value="{{ old('date', $today) }}" required>
                            @error('date')
                                <small class="text-danger mt-1 d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-2" style="border-radius: var(--radius-md);">
                            <i class="fas fa-info-circle mt-1"></i>
                            <div>
                                <span class="fw-semibold d-block">Informasi</span>
                                <span class="small opacity-75">Absensi ini adalah absensi kehadiran harian kelas.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-3 mb-4 reveal reveal-delay-2">
                    <button class="btn btn-primary btn-lg w-100" type="submit">
                        <i class="fas fa-check-circle"></i> Simpan Absensi
                    </button>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('guru.classroom.attendance', $class) }}">Batal</a>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="content-card reveal reveal-delay-2">
                    <div class="content-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="content-card-header-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="content-card-title">Status Kehadiran Siswa</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary-theme" id="markAllHadir">
                            <i class="fas fa-check-double"></i> Semua Hadir
                        </button>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">No</th>
                                        <th>Nama Siswa</th>
                                        <th class="text-center" style="width: 300px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr>
                                            <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $student->user?->name ?? 'Siswa (Tanpa Nama)' }}</div>
                                                <div class="text-muted small">NISN: {{ $student->nisn }}</div>
                                            </td>
                                            <td>
                                                <div class="status-radio-group">
                                                    <div class="status-radio-item">
                                                        <input type="radio" name="statuses[{{ $student->id }}]" id="h_{{ $student->id }}" value="hadir" checked required>
                                                        <label for="h_{{ $student->id }}">HADIR</label>
                                                    </div>
                                                    <div class="status-radio-item">
                                                        <input type="radio" name="statuses[{{ $student->id }}]" id="i_{{ $student->id }}" value="izin" required>
                                                        <label for="i_{{ $student->id }}">IZIN</label>
                                                    </div>
                                                    <div class="status-radio-item">
                                                        <input type="radio" name="statuses[{{ $student->id }}]" id="s_{{ $student->id }}" value="sakit" required>
                                                        <label for="s_{{ $student->id }}">SAKIT</label>
                                                    </div>
                                                    <div class="status-radio-item">
                                                        <input type="radio" name="statuses[{{ $student->id }}]" id="a_{{ $student->id }}" value="alpa" required>
                                                        <label for="a_{{ $student->id }}">ALPA</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @error('statuses')
                            <small class="text-danger mt-2 d-block">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('styles')
    <style>
        .status-radio-group {
            display: flex;
            gap: 6px;
            justify-content: center;
        }
        .status-radio-item {
            flex: 1;
        }
        .status-radio-item input[type="radio"] {
            display: none;
        }
        .status-radio-item label {
            display: block;
            padding: 8px 4px;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #6c757d;
            transition: all 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        .status-radio-item label:hover {
            background-color: rgba(27, 94, 32, 0.04);
            border-color: rgba(27, 94, 32, 0.15);
            color: var(--primary);
        }
        
        /* Hadir */
        .status-radio-item input[value="hadir"]:checked + label {
            background-color: rgba(67, 160, 71, 0.12);
            color: #2E7D32;
            border-color: #2E7D32;
            box-shadow: 0 2px 8px rgba(67, 160, 71, 0.1);
        }
        /* Izin */
        .status-radio-item input[value="izin"]:checked + label {
            background-color: rgba(249, 168, 37, 0.12);
            color: #B26A00;
            border-color: #B26A00;
            box-shadow: 0 2px 8px rgba(249, 168, 37, 0.1);
        }
        /* Sakit */
        .status-radio-item input[value="sakit"]:checked + label {
            background-color: rgba(255, 152, 0, 0.12);
            color: #E65100;
            border-color: #E65100;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.1);
        }
        /* Alpa */
        .status-radio-item input[value="alpa"]:checked + label {
            background-color: rgba(198, 40, 40, 0.10);
            color: #C62828;
            border-color: #C62828;
            box-shadow: 0 2px 8px rgba(198, 40, 40, 0.1);
        }
    </style>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('markAllHadir').addEventListener('click', () => {
                const radioButtons = document.querySelectorAll('input[type="radio"][value="hadir"]');
                radioButtons.forEach(radio => radio.checked = true);
            });
        });
    </script>
@endsection
