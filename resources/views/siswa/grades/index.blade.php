@extends('layouts.lms')

@section('title', 'Rekap Nilai Siswa')

@section('content')
    <div class="mb-4 reveal">
        <h1 class="mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--text-heading);">📊 Rekap Nilai</h1>
        <p class="mb-0" style="color: var(--text-muted);">Riwayat pengumpulan tugas dan nilai akhir Anda.</p>
    </div>

    @if($submissions->isEmpty())
        <div class="content-card reveal reveal-delay-1">
            <div class="empty-state py-5 text-center">
                <div class="empty-state-icon mb-3">
                    <div class="rounded-circle shadow-sm d-flex justify-content-center align-items-center mx-auto" style="width: 70px; height: 70px; font-size: 1.8rem; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
                <div class="empty-state-text">
                    <strong class="h5 fw-bold">Belum ada nilai</strong><br>
                    <p class="text-muted small mt-1">Anda belum memiliki riwayat pengumpulan tugas.</p>
                </div>
            </div>
        </div>
    @else
        <div class="content-card reveal reveal-delay-1 p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tugas</th>
                            <th class="px-4 py-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="font-size: 0.75rem; letter-spacing: 0.5px;">Mata Pelajaran</th>
                            <th class="px-4 py-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="font-size: 0.75rem; letter-spacing: 0.5px;">Guru</th>
                            <th class="px-4 py-3 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="font-size: 0.75rem; letter-spacing: 0.5px;">Dikumpulkan</th>
                            <th class="px-4 py-3 text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $sub)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('siswa.assignments.show', $sub->assignment_id) }}" class="fw-bold text-dark text-decoration-none" style="font-size: 0.95rem;">
                                        {{ Str::limit($sub->assignment->title, 40) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge bg-primary-subtle text-primary border-primary">
                                        <i class="fas fa-book me-1"></i> {{ $sub->assignment->subject->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-muted">
                                    <i class="fas fa-chalkboard-teacher me-1"></i> {{ $sub->assignment->teacher->user->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-muted">
                                    {{ \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($sub->score !== null)
                                        <span class="badge fw-bold" style="font-size: 0.95rem; background-color: {{ $sub->score >= 75 ? 'var(--success)' : 'var(--danger)' }}; color: white; padding: 0.4rem 0.8rem; border-radius: 8px;">
                                            {{ $sub->score }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark" style="font-size: 0.75rem;"><i class="fas fa-hourglass-half me-1"></i> Menunggu Penilaian</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
