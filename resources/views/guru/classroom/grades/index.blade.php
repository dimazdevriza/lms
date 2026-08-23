@extends('layouts.lms')

@section('title', 'Rekap Nilai - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">📊 Rekap Nilai Akumulasi Siswa</h1>
                    <p>Kelas {{ $class->name }} • Akumulasi nilai tugas online dari semua mata pelajaran & evaluasi rapor</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('guru.classroom.index') }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('guru.classroom.grades.input', $class) }}" class="btn btn-light d-inline-flex align-items-center gap-2" style="color: var(--primary) !important; font-weight: 700;">
                        <i class="fas fa-plus"></i> Input Nilai Evaluasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Nilai Siswa -->
    <div class="content-card reveal reveal-delay-1">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <i class="fas fa-calculator"></i>
            </div>
            <h5 class="content-card-title">Akumulasi Nilai Keseluruhan</h5>
        </div>
        <div class="content-card-body p-0">
            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 60px;">No.</th>
                                <th>📝 Nama Siswa</th>
                                <th class="text-center" style="width: 150px;">📚 Rata Tugas (All Mapel)</th>
                                <th class="text-center" style="width: 150px;">📝 Rata Evaluasi</th>
                                <th class="text-center" style="width: 180px;">🎯 Nilai Akhir Akumulasi</th>
                                <th class="text-center" style="width: 140px;">Rincian Mapel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    $acc = $accumulatedGrades[$student->id] ?? [
                                        'tugas_avg' => null,
                                        'eval_avg' => null,
                                        'overall_avg' => null,
                                        'total_evaluations' => 0,
                                        'subject_breakdown' => [],
                                    ];
                                    $overallAvg = $acc['overall_avg'];
                                    $badgeClass = $overallAvg >= 80 ? 'grade-badge--high' : ($overallAvg >= 70 ? 'grade-badge--mid' : ($overallAvg !== null ? 'grade-badge--low' : ''));
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-semibold text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $student->user?->name ?? 'Siswa (Tanpa Nama)' }}</div>
                                        <div class="text-muted small">NISN: {{ $student->nisn }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($acc['tugas_avg'] !== null)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold">
                                                {{ number_format($acc['tugas_avg'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($acc['eval_avg'] !== null)
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 fw-bold">
                                                {{ number_format($acc['eval_avg'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($overallAvg !== null)
                                            <span class="badge grade-badge {{ $badgeClass }}">
                                                {{ number_format($overallAvg, 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted fw-semibold">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalSubjectBreakdown{{ $student->id }}">
                                            <i class="fas fa-list-check me-1"></i> Mapel
                                        </button>

                                        <!-- Modal Detail Mapel -->
                                        <div class="modal fade text-start" id="modalSubjectBreakdown{{ $student->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light">
                                                        <h6 class="modal-header-title fw-bold mb-0">
                                                            <i class="fas fa-book-reader text-primary me-2"></i> Detail Nilai Mapel - {{ $student->user?->name ?? 'Siswa (Tanpa Nama)' }}
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-0">
                                                        <div class="list-group list-group-flush">
                                                            @forelse($acc['subject_breakdown'] as $subId => $subData)
                                                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                                                    <div>
                                                                        <div class="fw-bold text-dark">{{ $subData['subject_name'] }}</div>
                                                                        <small class="text-muted">{{ $subData['count'] }} penilaian tercatat</small>
                                                                    </div>
                                                                    <div>
                                                                        @if($subData['avg'] !== null)
                                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold" style="font-size: 0.9rem;">
                                                                                {{ number_format($subData['avg'], 1) }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted small">Belum ada nilai</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="p-4 text-center text-muted">Belum ada data nilai mata pelajaran.</div>
                                                            @endforelse
                                                        </div>
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
            @else
                <div class="empty-state py-5">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h5 class="empty-state-text mt-3">Belum Ada Siswa</h5>
                    <p class="text-muted mb-0">Tidak ada siswa yang terdaftar di kelas perwalian Anda saat ini.</p>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .grade-badge {
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
        }
        .grade-badge--high {
            background-color: rgba(67, 160, 71, 0.12);
            color: #2E7D32;
            border: 1px solid rgba(67, 160, 71, 0.2);
        }
        .grade-badge--mid {
            background-color: rgba(249, 168, 37, 0.12);
            color: #B26A00;
            border: 1px solid rgba(249, 168, 37, 0.2);
        }
        .grade-badge--low {
            background-color: rgba(198, 40, 40, 0.1);
            color: #C62828;
            border: 1px solid rgba(198, 40, 40, 0.2);
        }
    </style>
    @endpush
@endsection
