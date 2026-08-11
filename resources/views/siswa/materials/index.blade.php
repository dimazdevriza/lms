@extends('layouts.lms')

@section('title', 'Pembelajaran')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <h1><i class="fas fa-graduation-cap text-warning me-2"></i> Ruang Belajar</h1>
            <p>Akses materi dan tugas yang dikelompokkan per pertemuan</p>
        </div>
    </div>

    @if($meetings->isEmpty() && $standaloneMaterials->isEmpty())
        <div class="content-card reveal reveal-delay-1">
            <div class="content-card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="empty-state-text">Belum ada konten pembelajaran. Guru belum membagikan pertemuan atau materi untuk kelas Anda.</div>
                </div>
            </div>
        </div>
    @else
        <!-- Meetings List -->
        @foreach($meetings as $meeting)
            <div class="content-card mb-4 reveal reveal-delay-{{ min($loop->index + 1, 5) }}" style="border-left: 4px solid var(--primary);">
                <div class="content-card-header" style="padding-bottom: 16px;">
                    <div class="content-card-header-icon">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div class="flex-grow-1">
                        <span class="status-badge status-badge--aktif mb-2">Pertemuan ke-{{ $meeting->number }}</span>
                        <h5 class="content-card-title mb-1">{{ $meeting->title }}</h5>
                        <div class="small text-muted">
                            <span class="me-3"><i class="fas fa-book me-1"></i> {{ $meeting->subject->name }}</span>
                            <span class="me-3"><i class="fas fa-user-tie me-1"></i> {{ $meeting->teacher->user->name }}</span>
                            <span><i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($meeting->date)->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="content-card-body">
                    @if($meeting->description)
                        <p class="text-muted small border-bottom pb-3 mb-3 fst-italic">{{ $meeting->description }}</p>
                    @endif

                    <div class="row">
                        <!-- Materials for this meeting -->
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--primary); letter-spacing: 1px;"><i class="fas fa-book me-1"></i> Materi</h6>
                            @forelse($meeting->materials as $m)
                                <div class="d-flex align-items-center p-2 mb-2 bg-white rounded border item-hover-card" 
                                     onclick="window.open('{{ asset('storage/' . $m->file_path) }}', '_blank')">
                                    <i class="fas fa-file-pdf text-danger me-3 fa-lg"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small">{{ $m->title }}</div>
                                    </div>
                                    @if($m->file_path)
                                        <span class="btn btn-sm btn-outline-primary-theme">Buka</span>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted small fst-italic p-2">Tidak ada materi khusus untuk pertemuan ini.</div>
                            @endforelse
                        </div>

                        <!-- Assignments for this meeting -->
                        <div class="col-md-6">
                            <h6 class="text-uppercase small fw-bold mb-3" style="color: var(--accent); letter-spacing: 1px;"><i class="fas fa-edit me-1"></i> Tugas</h6>
                            @forelse($meeting->assignments as $a)
                                <div class="d-flex align-items-center p-2 mb-2 bg-white rounded border item-hover-card" 
                                     onclick="window.location='{{ route('siswa.assignments.index') }}'">
                                    <i class="fas fa-tasks me-3 fa-lg" style="color: var(--accent);"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold small">{{ $a->title }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">Deadline: {{ \Carbon\Carbon::parse($a->due_at)->format('d M Y, H:i') }}</div>
                                    </div>
                                    <span class="btn btn-sm btn-outline-accent-theme">Kerjakan</span>
                                </div>
                            @empty
                                <div class="text-muted small fst-italic p-2">Tidak ada tugas khusus untuk pertemuan ini.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Standalone Materials if any -->
        @if(!$standaloneMaterials->isEmpty())
            <div class="reveal reveal-delay-3">
                <h5 class="mt-5 mb-4" style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-folder-open me-2"></i> Materi Lainnya</h5>
                <div class="row">
                    @foreach($standaloneMaterials as $m)
                        <div class="col-md-6 mb-4">
                            <div class="content-card h-100">
                                <div class="content-card-header">
                                    <div class="content-card-header-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <h5 class="content-card-title">{{ $m->title }}</h5>
                                </div>
                                <div class="content-card-body">
                                    <div class="small text-muted mb-3">
                                        <span class="me-2"><i class="fas fa-book me-1"></i> {{ $m->subject->name }}</span>
                                        <span><i class="fas fa-calendar me-1"></i> {{ $m->created_at->format('d M Y') }}</span>
                                    </div>
                                    <p class="text-muted small mb-3">{{ Str::limit($m->content, 100) }}</p>
                                    @if($m->file_path)
                                        <a href="{{ asset('storage/' . $m->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary-theme w-100">
                                            <i class="fas fa-file-pdf me-1"></i> Lihat PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
@endsection
