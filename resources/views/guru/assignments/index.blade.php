@extends('layouts.lms')

@section('title', 'Pilih Kelas & Mapel - Tugas')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--text-heading);">📋 Kelola Tugas</h1>
            <p class="mb-0" style="color: var(--text-muted);">Pilih kelas dan mata pelajaran untuk mengelola tugas.</p>
        </div>
        <a class="btn btn-outline-secondary-theme" href="{{ route('guru.assignments.create') }}" style="border-radius: var(--radius-sm);">
            <i class="fas fa-plus me-2"></i> Buat Tugas Baru
        </a>
    </div>

    @if($teacherClasses->isEmpty())
        <div class="card border-0 shadow-sm reveal reveal-delay-1">
            <div class="card-body py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h5 class="fw-bold text-dark mt-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">Belum ada penugasan mengajar</h5>
                    <p class="empty-state-text mt-2">Anda belum ditugaskan untuk mengajar kelas manapun. Hubungi Tata Usaha.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($teacherClasses as $index => $tc)
                <div class="col-md-6 col-lg-4 mb-4 reveal reveal-delay-{{ min($index + 1, 5) }}">
                    <div class="card h-100 subject-card border-0" 
                         onclick="window.location='{{ route('guru.assignments.class-subject', ['class' => $tc->class_id, 'subject' => $tc->subject_id]) }}'">
                        
                        <div class="card-body p-4 text-center d-flex flex-column align-items-center">
                            <div class="subject-icon-circle shadow-sm mb-3">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                            
                            <h5 class="fw-bold mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary) !important;">{{ $tc->subject->name }}</h5>
                            <span class="badge bg-light text-secondary border px-3 py-1 rounded-pill mt-2 mb-3" style="font-weight: 600;">
                                <i class="fas fa-users me-1"></i> Kelas {{ $tc->schoolClass->name }}
                            </span>
                            
                            <div class="mt-auto w-100 pt-3 border-top">
                                <span class="status-badge status-badge--aktif w-100 justify-content-center py-2 bg-primary-subtle text-primary">
                                    <i class="fas fa-folder-open me-1"></i> Kelola Tugas
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <style>
        .subject-icon-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(27, 94, 32, 0.08), rgba(67, 160, 71, 0.04));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            transition: all 0.4s var(--ease-out);
            border-bottom: 4px solid var(--secondary);
        }
        .subject-card {
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            cursor: pointer;
            border-bottom: 4px solid transparent !important;
        }
        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
            border-bottom: 4px solid var(--primary) !important;
        }
    </style>
@endsection
