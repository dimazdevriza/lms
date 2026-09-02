@extends('layouts.lms')

@section('title', 'Pilih Mata Pelajaran')

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge mb-2">Siswa</span>
                    <h1 class="h3 mb-2" style="color: #FFFFFF !important; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800;"><i class="fas fa-tasks me-2 text-warning"></i> Daftar Tugas</h1>
                    <p class="text-white-50">Pilih mata pelajaran untuk melihat daftar tugas yang harus Anda kerjakan.</p>
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

    @if($subjects->isEmpty())
        <div class="card border-0 shadow-sm reveal reveal-delay-1">
            <div class="card-body py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mt-3" style="font-family: 'Plus Jakarta Sans', sans-serif;">Belum ada tugas</h5>
                    <p class="empty-state-text mt-2">Belum ada tugas apapun yang diberikan oleh guru untuk kelas Anda.</p>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($subjects as $index => $subject)
                <div class="col-md-6 col-lg-4 mb-4 reveal reveal-delay-{{ min($index + 1, 5) }}">
                    <div class="card h-100 subject-card border-0" 
                         onclick="window.location='{{ route('siswa.assignments.subject', $subject->id) }}'">
                        
                        <div class="card-body p-4 text-center d-flex flex-column align-items-center">
                            <div class="subject-icon-circle shadow-sm mb-3">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                            
                            <h5 class="fw-bold mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary) !important;">{{ $subject->name }}</h5>
                            <p class="text-muted small mb-4">Kode: {{ $subject->code ?? '-' }}</p>
                            
                            <div class="mt-auto w-100 pt-3 border-top">
                                <span class="status-badge status-badge--aktif w-100 justify-content-center py-2 bg-primary-subtle text-primary">
                                    <i class="fas fa-folder-open me-1"></i> Lihat Daftar Tugas
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
