@extends('layouts.lms')

@section('title', 'Pilih Pengajar')

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <a href="{{ route('siswa.subjects.index') }}" class="btn btn-sm btn-light text-primary mb-3" style="border-radius: 20px;">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Mata Pelajaran
                    </a>
                    <h1 class="h3 mb-2" style="color: #FFFFFF !important; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800;"><i class="fas fa-chalkboard-user me-2 text-warning"></i> Pilih Pengajar: {{ $subject->name }}</h1>
                    <p class="text-white-50">Silakan pilih guru pengajar untuk melihat materi dan tugas yang diberikan.</p>
                </div>
            </div>
        </div>
    </div>

    @if($teachers->isEmpty())
        <div class="card border-0 shadow-sm reveal reveal-delay-1">
            <div class="card-body py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h5 class="fw-bold text-dark mt-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">Belum ada pengajar</h5>
                    <p class="empty-state-text mt-2">Belum ada guru yang ditugaskan untuk mata pelajaran ini di kelas Anda.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($teachers as $index => $teacher)
                <div class="col-md-6 col-lg-4 mb-4 reveal reveal-delay-{{ min($index + 1, 5) }}">
                    <div class="card h-100 subject-card border-0 shadow-sm item-hover-card" 
                         onclick="window.location='{{ route('siswa.subjects.teacher.meetings', ['subject' => $subject->id, 'teacher' => $teacher->id]) }}'"
                         style="cursor: pointer; border-radius: var(--radius-md) !important; border-top: 4px solid var(--primary) !important;">
                        
                        <div class="card-body p-4 text-center d-flex flex-column align-items-center">
                            <div class="mb-3">
                                @if($teacher->user->avatar)
                                    <img src="{{ Storage::url($teacher->user->avatar) }}" alt="{{ $teacher->user->name }}" class="rounded-circle shadow-sm object-fit-cover border border-2 border-white" style="width: 80px; height: 80px;">
                                @else
                                    <div class="rounded-circle shadow-sm d-flex justify-content-center align-items-center bg-primary-subtle text-primary border border-2 border-white" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                                        {{ substr($teacher->user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            
                            <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $teacher->user->name }}</h5>
                            <p class="text-muted small mb-4">Guru {{ $subject->name }}</p>
                            
                            <div class="mt-auto w-100 pt-3 border-top">
                                <span class="status-badge status-badge--aktif w-100 justify-content-center py-2 bg-success-subtle text-success">
                                    <i class="fas fa-sign-in-alt me-1"></i> Masuk Kelas
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
