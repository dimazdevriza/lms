@extends('layouts.lms')

@section('title', 'Daftar Tugas')

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <a href="{{ route('siswa.assignments.index') }}" class="btn btn-sm btn-light text-primary mb-3" style="border-radius: 20px;">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Tugas
                    </a>
                    <h1 class="h3 mb-2" style="color: #FFFFFF !important; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800;"><i class="fas fa-tasks me-2 text-warning"></i> Tugas: {{ $subject->name }}</h1>
                    <p class="text-white-50">Daftar semua tugas yang diberikan untuk mata pelajaran ini.</p>
                </div>
            </div>
        </div>
    </div>

    @if(!$student)
        <div class="alert alert-warning reveal reveal-delay-1">
            <strong><i class="fas fa-exclamation-triangle me-1"></i> Perhatian</strong>
            <p class="mb-0 mt-2">Profil siswa belum terdaftar. Silakan minta Tata Usaha membuat data siswa untuk akun ini.</p>
        </div>
    @endif

    @if($assignments->isEmpty())
        <div class="content-card reveal reveal-delay-1">
            <div class="content-card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.05)); color: var(--accent);">
                        <i class="fas fa-clipboard-check fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Belum Ada Tugas</h5>
                    <p class="text-muted small mb-0">Belum ada tugas yang diberikan untuk mata pelajaran ini.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Assignments Grid -->
        <div class="row">
            @foreach($assignments as $assignment)
                @php
                    $isSubmitted = in_array($assignment->id, $submittedIds ?? []);
                    $isDeadlinePassed = $assignment->due_at && \Carbon\Carbon::parse($assignment->due_at)->isPast();
                @endphp
                <div class="col-md-6 col-lg-4 mb-4 reveal reveal-delay-{{ min($loop->index + 1, 5) }}">
                    <div class="card h-100 subject-card border-0 shadow-sm item-hover-card" 
                         onclick="window.location='{{ route('siswa.assignments.show', ['assignment' => $assignment->id, 'from' => 'assignments']) }}'"
                         style="cursor: pointer; border-radius: var(--radius-md) !important; border-top: 4px solid var(--primary) !important;">
                        
                        <div class="card-body p-4 text-center d-flex flex-column align-items-center">
                            <div class="mb-3">
                                <div class="rounded-circle shadow-sm d-flex justify-content-center align-items-center border border-2 border-white" style="width: 70px; height: 70px; font-size: 1.8rem; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                                    <i class="fas fa-tasks"></i>
                                </div>
                            </div>
                            
                            <span class="badge bg-light text-secondary border mb-2 px-3 py-1 rounded-pill" style="font-weight: 600;">{{ $subject->name }}</span>
                            <h5 class="fw-bold mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.4;" title="{{ $assignment->title }}">{{ Str::limit($assignment->title, 40) }}</h5>
                            
                            @if($isSubmitted)
                                <p class="text-success small mb-4 fw-bold"><i class="fas fa-check-circle me-1"></i> Telah Dikumpulkan</p>
                            @elseif($isDeadlinePassed)
                                <p class="text-danger small mb-4 fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> Terlewat</p>
                            @elseif($assignment->due_at)
                                <p class="text-muted small mb-4"><i class="fas fa-clock me-1 opacity-50"></i> {{ \Carbon\Carbon::parse($assignment->due_at)->format('d M, H:i') }}</p>
                            @else
                                <p class="text-muted small mb-4"><i class="fas fa-calendar-check me-1 opacity-50"></i> Tanpa Batas Waktu</p>
                            @endif
                            
                            <div class="mt-auto w-100 pt-3 border-top">
                                @if($isSubmitted)
                                    <span class="status-badge status-badge--aktif w-100 justify-content-center py-2 bg-success-subtle text-success" style="font-size: 0.85rem;">
                                        <i class="fas fa-eye me-2"></i> Lihat Hasil
                                    </span>
                                @elseif($isDeadlinePassed)
                                    <span class="status-badge status-badge--alpa w-100 justify-content-center py-2 bg-danger-subtle text-danger" style="font-size: 0.85rem;">
                                        <i class="fas fa-eye me-2"></i> Detail
                                    </span>
                                @else
                                    <span class="status-badge status-badge--aktif w-100 justify-content-center py-2 bg-primary-subtle text-primary" style="font-size: 0.85rem;">
                                        <i class="fas fa-edit me-2"></i> Kerjakan Tugas
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
