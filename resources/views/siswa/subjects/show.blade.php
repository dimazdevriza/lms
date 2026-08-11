@extends('layouts.lms')

@section('title', 'Daftar Pertemuan')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 mb-md-5 reveal">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            <a href="{{ route('siswa.subjects.index') }}" class="btn btn-outline-secondary-theme btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('siswa.subjects.index') }}" style="color: var(--secondary);">Mata Pelajaran</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
                    </ol>
                </nav>
                <h1 class="h3 mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary) !important;"><i class="fas fa-calendar-alt me-2 text-success"></i> Daftar Pertemuan: {{ $subject->name }}</h1>
            </div>
        </div>
        <div>
            <a href="{{ route('forum.index', ['classSlug' => $student->schoolClass->slug, 'subjectSlug' => $subject->slug]) }}" class="btn btn-primary-theme">
                <i class="fas fa-comments me-1"></i> Forum Diskusi
            </a>
        </div>
    </div>

    <div class="row">
        @forelse($meetings as $index => $meeting)
            <div class="col-md-6 mb-4 reveal reveal-delay-{{ min($index + 1, 5) }}">
                <div class="card h-100 meeting-card border-0" 
                     style="cursor: pointer; border-radius: var(--radius-md) !important; border-left: 6px solid var(--accent) !important;"
                     onclick="window.location='{{ route('siswa.meetings.show', $meeting->id) }}'">
                    
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="status-badge status-badge--aktif mb-2">Pertemuan ke-{{ $meeting->number }}</span>
                                <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $meeting->title }}</h5>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block"><i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($meeting->date)->format('d M Y') }}</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-chalkboard-user text-muted me-2"></i>
                            <span class="small text-muted">Guru: {{ $meeting->teacher->user->name }}</span>
                        </div>

                        <div class="d-flex gap-3 pt-3 border-top flex-wrap align-items-center">
                            <span class="small text-muted"><i class="fas fa-file-pdf me-1 text-danger"></i> Materi</span>
                            <span class="small text-muted"><i class="fas fa-tasks me-1 text-warning"></i> Tugas</span>
                            @if($meeting->video_link)
                                {{-- ponytail: direct video link button for student --}}
                                @if($meeting->video_link_status === 'finished')
                                    <span class="small text-muted fw-bold ms-auto" onclick="event.stopPropagation();">
                                        <i class="fas fa-video-slash me-1"></i> Kelas Selesai
                                    </span>
                                @else
                                    <a href="{{ $meeting->video_link }}" target="_blank" class="small text-primary fw-bold ms-auto" style="text-decoration: none;" onclick="event.stopPropagation();">
                                        <i class="fas fa-video me-1"></i> Gabung Kelas
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 reveal reveal-delay-1">
                <div class="empty-state bg-white rounded-4 shadow-sm py-5 border">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <p class="empty-state-text mt-2">Belum ada pertemuan untuk mata pelajaran ini.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if(!$standaloneMaterials->isEmpty())
        <div class="mt-5 mb-4 reveal reveal-delay-2">
            <h5 class="fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary) !important;"><i class="fas fa-folder-open me-2"></i> Materi Mandiri (Tanpa Pertemuan)</h5>
            <p class="text-muted small">Materi pendukung lainnya untuk mata pelajaran ini</p>
        </div>
        <div class="row">
            @foreach($standaloneMaterials as $index => $m)
                <div class="col-md-6 col-lg-4 mb-4 reveal reveal-delay-{{ min($index + 3, 5) }}">
                    <div class="card h-100 border-0 shadow-sm item-hover-card" style="border-radius: var(--radius-md) !important; border-top: 4px solid var(--secondary) !important;">
                        <div class="card-body p-4 d-flex flex-column">
                            <h6 class="fw-bold mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $m->title }}</h6>
                            <p class="text-muted small mb-4 flex-grow-1">{{ Str::limit($m->content, 80) }}</p>
                            <a href="{{ route('siswa.materials.show', $m) }}" class="btn btn-sm btn-outline-secondary-theme w-100 mt-auto">
                                <i class="fas fa-book-open me-1"></i> Buka Materi
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
