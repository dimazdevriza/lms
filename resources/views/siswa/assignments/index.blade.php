@extends('layouts.lms')

@section('title', 'Daftar Tugas')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.6rem;">📋 Daftar Tugas Saya</h1>
                <p class="mb-0 text-white-50" style="font-size: 0.9rem;">Pantau deadline dan kumpulkan tugas tepat waktu</p>
            </div>
        </div>
    </div>

    @if(!$student)
        <div class="alert alert-warning reveal reveal-delay-1">
            <strong>⚠️ Perhatian</strong>
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
                    <p class="text-muted small mb-0">Semua tugas yang diberikan oleh guru akan muncul di halaman ini.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Assignments Grid -->
        <div class="row">
            @forelse($assignments as $assignment)
                @php
                    $isSubmitted = in_array($assignment->id, $submittedIds ?? []);
                    $isDeadlinePassed = $assignment->due_at && \Carbon\Carbon::parse($assignment->due_at)->isPast();
                    $isDeadlineSoon = $assignment->due_at && !$isDeadlinePassed && \Carbon\Carbon::parse($assignment->due_at)->diffInHours(now()) <= 24;

                    $fileExtension = $assignment->file_path ? pathinfo($assignment->file_path, PATHINFO_EXTENSION) : 'docx';
                    $fileIconClass = match(strtolower($fileExtension)) {
                        'pdf' => 'fa-file-pdf text-danger',
                        'doc', 'docx' => 'fa-file-word text-primary',
                        'xls', 'xlsx' => 'fa-file-excel text-success',
                        'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                        default => 'fa-file-alt text-secondary'
                    };
                @endphp
                <div class="col-xl-4 col-md-6 mb-4 reveal reveal-delay-{{ ($loop->index % 3) + 1 }}">
                    <div class="content-card h-100 d-flex flex-column justify-content-between shadow-sm border-0 overflow-hidden" 
                         style="cursor: pointer; border-radius: var(--radius-md); transition: transform 0.2s ease, box-shadow 0.2s ease;"
                         onclick="window.location='{{ route('siswa.assignments.show', $assignment) }}'">
                        
                        <div class="content-card-body p-3.5 flex-grow-1">
                            <!-- Top Info: Class & Subject -->
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <span class="small text-muted text-truncate" style="font-size: 0.78rem;">
                                    <i class="fas fa-book me-1" style="color: var(--secondary);"></i>{{ $assignment->subject?->name ?? 'Mata Pelajaran' }}
                                    <span class="mx-1">•</span>
                                    <i class="fas fa-door-open me-1" style="color: var(--primary);"></i>{{ $assignment->schoolClass?->name }}
                                </span>

                                @if($isSubmitted)
                                    <span class="status-badge status-badge--hadir py-0 px-2 flex-shrink-0" style="font-size: 0.7rem;"><i class="fas fa-check me-1"></i>Dikumpulkan</span>
                                @elseif($isDeadlinePassed)
                                    <span class="status-badge status-badge--alpa py-0 px-2 flex-shrink-0" style="font-size: 0.7rem;"><i class="fas fa-times-circle me-1"></i>Terlewat</span>
                                @else
                                    <span class="status-badge status-badge--aktif py-0 px-2 flex-shrink-0" style="font-size: 0.7rem;"><i class="fas fa-clock me-1"></i>Aktif</span>
                                @endif
                            </div>

                            <!-- Assignment Title -->
                            <h6 class="fw-bold mb-1 text-truncate" style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;" title="{{ $assignment->title }}">
                                {{ $assignment->title }}
                            </h6>

                            <!-- Type Badge & File Info -->
                            <div class="d-flex align-items-center gap-1.5 mb-2.5 flex-wrap">
                                @if($assignment->isOnline())
                                    <span class="status-badge py-0 px-2" style="background: rgba(13,110,253,0.1); color: #0d6efd; font-size: 0.7rem;"><i class="fas fa-laptop me-1"></i>Soal Online</span>
                                @elseif($assignment->type === 'external')
                                    <span class="status-badge py-0 px-2" style="background: rgba(25,135,84,0.1); color: var(--primary); font-weight: 700; font-size: 0.7rem;"><i class="fas fa-link me-1"></i>Kuis Online</span>
                                @else
                                    <span class="status-badge py-0 px-2" style="background: rgba(108,117,125,0.1); color: #6c757d; font-size: 0.7rem;">
                                        <i class="fas {{ $fileIconClass }} me-1"></i>{{ strtoupper($fileExtension) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Description excerpt -->
                            @if($assignment->description)
                                <p class="text-muted small mb-3 text-truncate-2" style="font-size: 0.82rem; line-height: 1.35; max-height: 2.7em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    {{ $assignment->description }}
                                </p>
                            @endif

                            <!-- Deadline Box -->
                            @if($assignment->due_at)
                                <div class="bg-light rounded p-2 mb-3 small" style="font-size: 0.78rem;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted"><i class="fas fa-clock {{ $isDeadlinePassed ? 'text-danger' : ($isDeadlineSoon ? 'text-warning' : 'text-primary') }} me-1"></i> Deadline:</span>
                                        <strong class="{{ $isDeadlinePassed ? 'text-danger' : ($isDeadlineSoon ? 'text-warning' : 'text-dark') }}">
                                            {{ \Carbon\Carbon::parse($assignment->due_at)->format('d M Y, H:i') }}
                                        </strong>
                                    </div>
                                    @if(!$isDeadlinePassed)
                                        <div class="text-end text-muted mt-0.5" style="font-size: 0.72rem;">
                                            ({{ \Carbon\Carbon::parse($assignment->due_at)->diffForHumans() }})
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Card Action Footer -->
                        <div class="px-3 py-2.5 bg-white d-flex justify-content-between align-items-center" onclick="event.stopPropagation();" style="border-top: 1px solid rgba(0,0,0,0.05);">
                            <span class="small text-muted" style="font-size: 0.75rem;">
                                @if($assignment->meeting)
                                    <i class="fas fa-calendar-alt text-warning me-1"></i>Pertemuan {{ $assignment->meeting->number }}
                                @endif
                            </span>
                            @if($isSubmitted)
                                <a href="{{ route('siswa.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-success py-1 px-3" style="border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-check-circle me-1"></i> Lihat Hasil
                                </a>
                            @elseif($isDeadlinePassed)
                                <a href="{{ route('siswa.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-secondary py-1 px-3" style="border-radius: var(--radius-sm); font-size: 0.8rem;">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                            @else
                                <a href="{{ route('siswa.assignments.show', $assignment) }}" class="btn btn-sm btn-primary py-1 px-3" style="border-radius: var(--radius-sm); font-size: 0.8rem; font-weight: 600;">
                                    <i class="fas fa-edit me-1"></i> Kerjakan
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    @endif
@endsection
