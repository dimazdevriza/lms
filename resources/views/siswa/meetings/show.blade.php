@extends('layouts.lms')

@section('title', 'Detail Pertemuan')

@section('content')
    <div class="mb-5 reveal">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('siswa.subjects.index') }}" style="color: var(--secondary);">Mata Pelajaran</a></li>
                <li class="breadcrumb-item"><a href="{{ route('siswa.subjects.show', $meeting->subject_id) }}" style="color: var(--secondary);">{{ $meeting->subject->name }}</a></li>
                <li class="breadcrumb-item active">Pertemuan {{ $meeting->number }}</li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">
            <a href="{{ route('siswa.subjects.show', $meeting->subject_id) }}" class="btn btn-outline-secondary-theme btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <span class="status-badge status-badge--aktif mb-2">Pertemuan ke-{{ $meeting->number }}</span>
                <h1 class="h3 mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary) !important;">{{ $meeting->title }}</h1>
                <div class="text-muted small">
                    <span class="me-3"><i class="fas fa-chalkboard-user me-1"></i> {{ $meeting->teacher->user->name }}</span>
                    <span class="me-3"><i class="fas fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($meeting->date)->format('d M Y') }}</span>
                    @if($block = $meeting->schedule_block)
                        <span><i class="fas fa-clock me-1"></i> {{ $block->jp_count }} JP ({{ \Carbon\Carbon::parse($block->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($block->end_time)->format('H:i') }})</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($meeting->video_link)
        {{-- ponytail: simple video link alert callout --}}
        @if($meeting->video_link_status === 'finished')
            <div class="alert alert-secondary d-flex align-items-center justify-content-between p-4 mb-4 border-0 shadow-sm reveal" style="border-radius: var(--radius-md) !important; border-left: 5px solid #6c757d !important; background-color: #f8f9fa;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-white rounded-circle shadow-sm text-secondary">
                        <i class="fas fa-video-slash fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-secondary">Sesi Video Conference Telah Selesai</h6>
                        <p class="mb-0 text-muted small">Kelas virtual untuk pertemuan ini telah diselesaikan oleh guru.</p>
                    </div>
                </div>
                <button class="btn btn-secondary px-4 py-2 disabled" style="border: none; border-radius: 10px; font-weight: 600; opacity: 0.65;">
                    <i class="fas fa-check-circle me-2"></i> Selesai
                </button>
            </div>
        @else
            <div class="alert alert-info d-flex align-items-center justify-content-between p-4 mb-4 border-0 shadow-sm reveal" style="border-radius: var(--radius-md) !important; border-left: 5px solid var(--primary-light) !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-white rounded-circle shadow-sm" style="color: var(--primary);">
                        <i class="fas fa-video fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1" style="color: var(--primary);">Sesi Video Conference Aktif</h6>
                        <p class="mb-0 text-muted small">Silakan bergabung ke kelas virtual menggunakan tautan di samping.</p>
                    </div>
                </div>
                <a href="{{ $meeting->video_link }}" target="_blank" class="btn btn-primary px-4 py-2" style="background-color: var(--primary); border: none; border-radius: 10px; font-weight: 600;">
                    <i class="fas fa-external-link-alt me-2"></i> Gabung Kelas
                </a>
            </div>
        @endif
    @endif

    @if($meeting->description)
        <div class="card mb-5 border-0 shadow-sm reveal reveal-delay-1" style="border-radius: var(--radius-md) !important; border-left: 5px solid var(--accent) !important;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-2 text-muted" style="font-family: 'Plus Jakarta Sans', sans-serif;">Deskripsi / Instruksi:</h6>
                <p class="mb-0 text-dark">{{ $meeting->description }}</p>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Materi Section -->
        <div class="col-lg-6 mb-4 reveal reveal-delay-2">
            <div class="content-card h-100">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5 class="content-card-title">Materi Pembelajaran</h5>
                </div>
                <div class="content-card-body">
                    @forelse($meeting->materials as $m)
                        <div class="d-flex align-items-center p-3 mb-3 rounded border-start border-4 border-success item-hover-card" 
                             style="background-color: var(--bg-body);"
                             onclick="window.location='{{ route('siswa.materials.show', $m) }}'">
                            <i class="fas fa-book-open text-success fa-2x me-3"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fw-bold text-dark">{{ $m->title }}</h6>
                                <p class="text-muted small mb-0">{{ Str::limit($m->content, 50) }}</p>
                            </div>
                            <!-- ponytail: hide cosmetic Buka button on mobile to prevent text squishing -->
                            <span class="btn btn-sm btn-secondary rounded-pill px-3 d-none d-sm-inline-block">Buka</span>
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <p class="empty-state-text">Tidak ada materi untuk pertemuan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Tugas Section -->
        <div class="col-lg-6 mb-4 reveal reveal-delay-3">
            <div class="content-card h-100">
                <div class="content-card-header">
                    <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.06)); color: var(--accent);">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h5 class="content-card-title" style="color: var(--accent) !important;">Tugas Siswa</h5>
                </div>
                <div class="content-card-body">
                    @forelse($meeting->assignments as $a)
                        <div class="p-4 mb-4 rounded border-start border-4 border-warning" style="background-color: var(--bg-body); border-radius: var(--radius-md) !important;">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-edit text-warning fa-2x me-3"></i>
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">{{ $a->title }}</h6>
                                        <small class="text-danger fw-bold"><i class="fas fa-clock me-1"></i> Deadline: {{ \Carbon\Carbon::parse($a->due_at)->format('d M Y, H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                            
                            @if($a->file_path)
                                <!-- ponytail: use secure download route instead of asset('storage') link -->
                                <a href="{{ route('assignments.download', $a) }}" target="_blank" class="btn btn-sm btn-outline-danger w-100 mb-3" style="border-radius: var(--radius-sm);">
                                    <i class="fas fa-file-pdf me-1"></i> Lihat PDF Soal
                                </a>
                            @endif

                            @php
                                $submission = $a->submissions()->where('student_id', $student->id)->first();
                            @endphp

                            @if($submission)
                                <div class="alert alert-success py-2 px-3 small border-0 mb-0">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <span><i class="fas fa-check-circle me-1"></i> Anda sudah mengumpulkan tugas ini.</span>
                                        <a href="{{ route('siswa.assignments.show', $a) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-edit me-1"></i> Lihat / Edit Jawaban
                                        </a>
                                    </div>
                                </div>
                            @elseif($a->due_at && \Carbon\Carbon::parse($a->due_at)->isPast())
                                <div class="alert alert-danger py-2 px-3 small border-0 mb-0">
                                    <i class="fas fa-times-circle me-1"></i> Deadline sudah lewat.
                                </div>
                            @elseif($a->type === 'online')
                                <a href="{{ route('siswa.assignments.show', $a) }}" class="btn btn-sm btn-primary w-100" style="border-radius: var(--radius-sm);">
                                    <i class="fas fa-laptop me-1"></i> Kerjakan Online
                                </a>
                            @else
                                <form method="POST" action="{{ route('siswa.assignments.submit', $a) }}" enctype="multipart/form-data" onsubmit="var btn = this.querySelector('button[type=submit]'); if (btn) { btn.disabled = true; btn.innerHTML = '<i class=\'fas fa-spinner fa-spin me-1\'></i> Mengirim...'; }">
                                    @csrf
                                    <div class="mb-3">
                                        <textarea class="form-control form-control-sm" name="answer_text" placeholder="Catatan/Jawaban singkat (opsional)..." rows="2" style="border-radius: var(--radius-sm);"></textarea>
                                    </div>
                                     <div class="mb-4">
                                         <label class="form-label small text-muted mb-1 fw-bold">Unggah File Jawaban (PDF, Word, Excel, PPT)</label>
                                         <input type="file" class="form-control form-control-sm" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" onchange="validateFileSize(this)" required style="border-radius: var(--radius-sm);">
                                         <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;"><i class="fas fa-info-circle me-1"></i> Maksimal ukuran file: 10 MB</small>
                                     </div>
                                    <button class="btn btn-sm btn-secondary w-100" type="submit" style="background-color: var(--primary); border: none; border-radius: var(--radius-sm); font-weight: 600; padding: 0.6rem;">Kirim Jawaban</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state">
                            <div class="empty-state-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.06), rgba(249, 168, 37, 0.03));">
                                <i class="fas fa-clipboard-list" style="color: var(--accent); opacity: 0.5;"></i>
                            </div>
                            <p class="empty-state-text">Tidak ada tugas untuk pertemuan ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Discussion Forum --}}
    @include('partials.meeting-discussion', ['meeting' => $meeting, 'discussionPosts' => $discussionPosts])
@endsection
