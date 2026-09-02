@extends('layouts.lms')

@section('title', 'Tugas: ' . $subject->name . ' - Kelas ' . $class->name)

@section('content')
    <!-- Header -->
    <div class="mb-4 reveal">
        <a href="{{ route('guru.assignments.index') }}" class="btn btn-outline-secondary-theme btn-sm mb-3">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Kelas
        </a>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h1 class="mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--text-heading);">📋 Kelola Tugas: {{ $subject->name }}</h1>
                <p class="mb-0" style="color: var(--text-muted);"><i class="fas fa-users me-1 text-primary"></i> Kelas {{ $class->name }}</p>
            </div>
            <a class="btn btn-outline-secondary-theme" href="{{ route('guru.assignments.create', ['class_id' => $class->id, 'subject_id' => $subject->id]) }}" style="border-radius: var(--radius-sm);">
                <i class="fas fa-plus me-2"></i> Buat Tugas
            </a>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="content-card reveal reveal-delay-1">
            <div class="empty-state py-5 text-center">
                <div class="empty-state-icon mb-3">
                    <div class="rounded-circle shadow-sm d-flex justify-content-center align-items-center border border-2 border-white mx-auto" style="width: 70px; height: 70px; font-size: 1.8rem; background-color: rgba(13, 110, 253, 0.1); color: #0d6efd;">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="empty-state-text">
                    <strong class="h5 fw-bold">Belum ada tugas</strong><br>
                    <p class="text-muted small mt-1">Mulai dengan membuat tugas baru untuk kelas ini.</p>
                </div>
                <a href="{{ route('guru.assignments.create', ['class_id' => $class->id, 'subject_id' => $subject->id]) }}" class="btn btn-primary mt-3" style="border-radius: var(--radius-sm);">Buat Tugas Baru</a>
            </div>
        </div>
    @else
        <div class="row">
            @forelse($assignments as $a)
                <div class="col-xl-4 col-md-6 mb-4 reveal reveal-delay-{{ min($loop->index + 1, 5) }}">
                    <div class="content-card h-100 d-flex flex-column justify-content-between shadow-sm border-0" 
                         style="cursor: pointer; border-radius: var(--radius-md); transition: transform 0.2s ease, box-shadow 0.2s ease;"
                         onclick="window.location='{{ route('guru.assignments.show', $a) }}'">
                        
                        <div class="content-card-body p-3 flex-grow-1">
                            <!-- Top Title & Badge -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h5 class="fw-bold mb-1 text-truncate" title="{{ $a->title }}" style="font-size: 1.1rem; color: var(--text-heading);">{{ $a->title }}</h5>
                                </div>
                                <span class="badge {{ \Carbon\Carbon::parse($a->due_at)->isPast() ? 'bg-danger' : 'bg-primary' }} mt-1" style="font-weight: 500; padding: 0.4rem 0.6rem;">
                                    {{ \Carbon\Carbon::parse($a->due_at)->isPast() ? 'Selesai' : 'Aktif' }}
                                </span>
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="fas fa-clock me-1"></i> Tenggat: 
                                <span class="{{ \Carbon\Carbon::parse($a->due_at)->isPast() ? 'text-danger fw-bold' : '' }}">
                                    {{ \Carbon\Carbon::parse($a->due_at)->format('d M Y H:i') }}
                                </span>
                            </p>

                            <!-- Submission Stats -->
                            <div class="d-flex gap-3 mb-2 px-2 py-2 rounded" style="background-color: var(--bg-body);">
                                <div class="text-center flex-fill">
                                    <div class="h5 mb-0 fw-bold text-success">{{ $a->submissions->where('status', 'graded')->count() }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Dinilai</small>
                                </div>
                                <div class="border-end"></div>
                                <div class="text-center flex-fill">
                                    <div class="h5 mb-0 fw-bold text-warning">{{ $a->submissions->where('status', 'submitted')->count() }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Menunggu</small>
                                </div>
                                <div class="border-end"></div>
                                <div class="text-center flex-fill">
                                    <div class="h5 mb-0 fw-bold text-secondary">{{ $a->schoolClass->students->count() ?? 0 }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Total Siswa</small>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-3 border-top bg-light text-center" style="border-radius: 0 0 var(--radius-md) var(--radius-md);">
                            <span class="text-primary small fw-bold">
                                <i class="fas fa-search me-1"></i> Lihat Detail & Nilai
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Handled by $assignments->isEmpty() above -->
            @endforelse
        </div>

        <!-- Pagination -->
        @if($assignments->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $assignments->links() }}
            </div>
        @endif
    @endif
    
    <style>
        .content-card:hover {
            transform: translateY(-4px) !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
    </style>
@endsection
