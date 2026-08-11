@extends('layouts.lms')

@section('title', 'Direktori Kelas & Guru')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="mb-1"><i class="fas fa-users text-warning me-2"></i> Direktori Kelas</h1>
                    <p class="mb-0">Daftar teman sekelas dan guru pengajar Anda</p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 0.85rem;">
                        {{ $schoolClass->name }}
                    </span>
                    @if($schoolClass->major)
                        <span class="badge bg-white text-success px-3 py-2 rounded-pill fw-bold shadow-sm ms-1" style="font-size: 0.85rem;">
                            {{ $schoolClass->major }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 reveal" id="directoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 py-2 fw-bold" id="classmates-tab" data-bs-toggle="pill" data-bs-target="#classmates" type="button" role="tab" aria-controls="classmates" aria-selected="true" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                <i class="fas fa-user-friends me-1"></i> Teman Sekelas ({{ $classmates->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 py-2 fw-bold" id="teachers-tab" data-bs-toggle="pill" data-bs-target="#teachers" type="button" role="tab" aria-controls="teachers" aria-selected="false" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                <i class="fas fa-chalkboard-teacher me-1"></i> Guru & Pengajar
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="directoryTabsContent">
        <!-- Tab 1: Classmates -->
        <div class="tab-pane fade show active" id="classmates" role="tabpanel" aria-labelledby="classmates-tab">
            <div class="row reveal">
                @forelse($classmates as $c)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="content-card h-100 border-0 shadow-sm p-4 d-flex align-items-center gap-3" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                            <!-- Avatar Circle -->
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold shadow-sm" style="width: 54px; height: 54px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); font-size: 1.2rem; font-family: 'Plus Jakarta Sans', sans-serif; flex-shrink: 0;">
                                {{ strtoupper(substr($c->user->name, 0, 2)) }}
                            </div>
                            <!-- Details -->
                            <div class="overflow-hidden">
                                <h6 class="mb-1 text-dark fw-bold text-truncate" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1rem;" title="{{ $c->user->name }}">
                                    {{ $c->user->name }}
                                </h6>
                                <div class="small text-muted d-flex align-items-center gap-1 mb-1">
                                    <i class="fas fa-id-card text-success" style="width: 14px;"></i> NISN: {{ $c->nisn }}
                                </div>
                                <div class="small text-muted d-flex align-items-center gap-1">
                                    <i class="fas fa-phone text-success" style="width: 14px;"></i> {{ $c->phone ?? 'Tidak ada kontak' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-user-friends fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
                            <p class="text-muted">Tidak ada teman sekelas lain yang terdaftar.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Tab 2: Teachers -->
        <div class="tab-pane fade" id="teachers" role="tabpanel" aria-labelledby="teachers-tab">
            <!-- Wali Kelas Banner -->
            @if($homeroomTeacher)
                <div class="content-card border-0 shadow-sm p-4 mb-4 reveal" style="border-radius: 16px; border-left: 5px solid var(--accent) !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold shadow-sm" style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent), #fbc02d); font-size: 1.3rem; font-family: 'Plus Jakarta Sans', sans-serif; flex-shrink: 0;">
                                {{ strtoupper(substr($homeroomTeacher->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <span class="badge bg-warning-subtle text-warning mb-1 fw-bold px-3 py-1 rounded-pill" style="font-size: 0.75rem;"><i class="fas fa-star text-warning me-1"></i> WALI KELAS</span>
                                <h5 class="mb-1 text-dark fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $homeroomTeacher->user->name }}</h5>
                                <div class="small text-muted d-flex align-items-center gap-2">
                                    <span><i class="fas fa-id-badge text-warning"></i> NIP: {{ $homeroomTeacher->nip ?? '-' }}</span>
                                    <span>•</span>
                                    <span><i class="fas fa-phone text-warning"></i> {{ $homeroomTeacher->phone ?? 'Tidak ada kontak' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Subject Teachers List -->
            <div class="row reveal">
                <div class="col-12 mb-3">
                    <h6 class="text-muted fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.9rem;"><i class="fas fa-book text-success me-1"></i> Guru Mata Pelajaran:</h6>
                </div>

                @forelse($subjectTeachers->unique('teacher_id') as $st)
                    @if(!$homeroomTeacher || $st->teacher_id !== $homeroomTeacher->id)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="content-card h-100 border-0 shadow-sm p-4 d-flex align-items-center gap-3" style="border-radius: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                                <!-- Avatar -->
                                <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold shadow-sm" style="width: 54px; height: 54px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); font-size: 1.2rem; font-family: 'Plus Jakarta Sans', sans-serif; flex-shrink: 0;">
                                    {{ strtoupper(substr($st->teacher->user->name, 0, 2)) }}
                                </div>
                                <!-- Details -->
                                <div class="overflow-hidden">
                                    <span class="badge bg-success-subtle text-success mb-1 fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.7rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                                        {{ $st->subject->name }}
                                    </span>
                                    <h6 class="mb-1 text-dark fw-bold text-truncate" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1rem;" title="{{ $st->teacher->user->name }}">
                                        {{ $st->teacher->user->name }}
                                    </h6>
                                    <div class="small text-muted d-flex align-items-center gap-1 mb-1">
                                        <i class="fas fa-id-badge text-success" style="width: 14px;"></i> NIP: {{ $st->teacher->nip ?? '-' }}
                                    </div>
                                    <div class="small text-muted d-flex align-items-center gap-1">
                                        <i class="fas fa-phone text-success" style="width: 14px;"></i> {{ $st->teacher->phone ?? 'Tidak ada kontak' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="empty-state">
                            <i class="fas fa-chalkboard-teacher fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
                            <p class="text-muted">Belum ada guru mata pelajaran yang terdaftar.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Style for Hover Effects -->
    <style>
        .content-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(37, 103, 30, 0.08) !important;
        }
        #directoryTabs .nav-link {
            color: var(--text-muted, #718096) !important;
            background: transparent;
            border: 1px solid rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        #directoryTabs .nav-link.active {
            color: #fff !important;
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }
        #directoryTabs .nav-link:hover:not(.active) {
            background-color: rgba(37, 103, 30, 0.04) !important;
            border-color: var(--primary-light) !important;
            color: var(--primary) !important;
        }
    </style>
@endsection
