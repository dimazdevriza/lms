@extends('layouts.lms')

@section('title', 'Tugas')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--text-heading);">📋 Kelola Tugas</h1>
            <p class="mb-0" style="color: var(--text-muted);">Buat dan kelola tugas untuk siswa Anda</p>
        </div>
        <a class="btn btn-outline-secondary-theme" href="{{ route('guru.assignments.create') }}" style="border-radius: var(--radius-sm);">
            <i class="fas fa-plus me-2"></i> Buat Tugas Baru
        </a>
    </div>

    <!-- Filter Kelas -->
    @if(isset($teacherClasses) && $teacherClasses->count() > 0)
        <div class="d-flex gap-2 mb-4 flex-wrap align-items-center">
            <span class="small fw-bold me-1" style="color: var(--text-muted);"><i class="fas fa-filter me-1"></i>Kelas:</span>
            <a href="{{ route('guru.assignments.index') }}"
               class="btn btn-sm {{ !$selectedClassId ? 'text-white' : 'btn-outline-secondary' }}"
               style="{{ !$selectedClassId ? 'background-color: var(--primary); border-color: var(--primary);' : '' }} border-radius: var(--radius-sm);">
                Semua Kelas
            </a>
            @foreach($teacherClasses as $cls)
                <a href="{{ route('guru.assignments.index', ['class_id' => $cls->id]) }}"
                   class="btn btn-sm {{ $selectedClassId == $cls->id ? 'text-white' : 'btn-outline-secondary' }}"
                   style="{{ $selectedClassId == $cls->id ? 'background-color: var(--primary); border-color: var(--primary);' : '' }} border-radius: var(--radius-sm);">
                    {{ $cls->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($assignments->isEmpty())
        <div class="content-card">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="empty-state-text">
                    <strong>Belum ada tugas</strong><br>
                    Mulai dengan membuat tugas baru untuk siswa Anda.
                </div>
                <a href="{{ route('guru.assignments.create') }}" class="btn btn-outline-secondary-theme mt-3" style="border-radius: var(--radius-sm);">Buat Sekarang</a>
            </div>
        </div>
    @else
        <div class="row">
            @forelse($assignments as $a)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="content-card h-100 d-flex flex-column justify-content-between shadow-sm border-0" 
                         style="cursor: pointer; border-radius: var(--radius-md); transition: transform 0.2s ease, box-shadow 0.2s ease;"
                         onclick="window.location='{{ route('guru.assignments.show', $a) }}'">
                        
                        <div class="content-card-body p-3 flex-grow-1">
                            <!-- Top Title & Badge -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="fw-bold mb-1 text-truncate" style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;" title="{{ $a->title }}">
                                        {{ $a->title }}
                                    </h6>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        @if($a->due_at && \Carbon\Carbon::parse($a->due_at)->isFuture())
                                            <span class="status-badge status-badge--hadir py-0 px-2" style="font-size: 0.7rem;">✓ Aktif</span>
                                        @else
                                            <span class="status-badge py-0 px-2" style="background: rgba(220,53,69,0.1); color: #C62828; font-size: 0.7rem;">⊘ Ditutup</span>
                                        @endif

                                        @if($a->isOnline())
                                            <span class="status-badge py-0 px-2" style="background: rgba(13,110,253,0.1); color: #0d6efd; font-size: 0.7rem;"><i class="fas fa-laptop me-1"></i>Online</span>
                                        @elseif($a->type === 'external')
                                            <span class="status-badge py-0 px-2" style="background: rgba(25,135,84,0.1); color: var(--primary); font-weight: 700; font-size: 0.7rem;"><i class="fas fa-link me-1"></i>Kuis</span>
                                        @else
                                            <span class="status-badge py-0 px-2" style="background: rgba(108,117,125,0.1); color: #6c757d; font-size: 0.7rem;"><i class="fas fa-file-alt me-1"></i>Dokumen</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-light text-muted fw-normal flex-shrink-0" style="font-size: 0.7rem;">
                                    <i class="fas fa-calendar-check me-1"></i>{{ $a->created_at->format('d M Y') }}
                                </span>
                            </div>

                            <!-- Class & Subject -->
                            <div class="small text-muted mb-2.5 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.8rem;">
                                <span><i class="fas fa-door-open me-1" style="color: var(--primary);"></i>{{ $a->schoolClass?->name ?? 'Tanpa Kelas' }}</span>
                                <span>•</span>
                                <span><i class="fas fa-book me-1" style="color: var(--secondary);"></i>{{ $a->subject?->name ?? 'Tanpa Mapel' }}</span>
                            </div>

                            <!-- Deadline & Meeting Info -->
                            @if($a->due_at || $a->meeting)
                                <div class="bg-light rounded p-2 mb-3 small" style="font-size: 0.78rem;">
                                    @if($a->due_at)
                                        <div class="d-flex align-items-center justify-content-between {{ $a->meeting ? 'mb-1' : '' }}">
                                            <span class="text-muted"><i class="fas fa-clock text-danger me-1"></i> Deadline:</span>
                                            <strong class="text-danger">{{ \Carbon\Carbon::parse($a->due_at)->format('d M Y, H:i') }}</strong>
                                        </div>
                                    @endif
                                    @if($a->meeting)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted"><i class="fas fa-calendar-alt text-warning me-1"></i> Pertemuan:</span>
                                            <strong style="color: var(--primary);">Pertemuan {{ $a->meeting->number }}</strong>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Submission count & attachment -->
                            <div class="d-flex justify-content-between align-items-center small" style="font-size: 0.8rem;">
                                <span class="fw-semibold text-muted">
                                    <i class="fas fa-user-check me-1" style="color: var(--primary);"></i> {{ $a->submissions_count ?? $a->submissions->count() }} Terkumpul
                                </span>
                                @if($a->file_path)
                                    @php
                                        $fileExtension = pathinfo($a->file_path, PATHINFO_EXTENSION);
                                        $fileIcon = match(strtolower($fileExtension)) {
                                            'pdf' => 'fa-file-pdf text-danger',
                                            'doc', 'docx' => 'fa-file-word text-primary',
                                            'xls', 'xlsx' => 'fa-file-excel text-success',
                                            'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                            default => 'fa-file-alt text-secondary'
                                        };
                                    @endphp
                                    <span class="status-badge py-0 px-2" style="background: rgba(13,110,253,0.08); color: #0d6efd; font-size: 0.75rem;" onclick="event.stopPropagation(); window.open('{{ route('assignments.download', $a) }}', '_blank')">
                                        <i class="fas {{ $fileIcon }} me-1"></i>{{ strtoupper($fileExtension) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Actions Footer -->
                        <div class="px-3 py-2 bg-white d-flex justify-content-end gap-2 align-items-center" onclick="event.stopPropagation();" style="border-top: 1px solid rgba(0,0,0,0.05);">
                            <a href="{{ route('guru.assignments.edit', $a) }}" class="btn btn-sm btn-outline-primary py-1 px-3" style="border-radius: var(--radius-sm); font-size: 0.8rem;">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('guru.assignments.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="border-radius: var(--radius-sm); font-size: 0.8rem;" title="Hapus Tugas">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>

        <div class="mt-4">{{ $assignments->links() }}</div>
    @endif
@endsection
