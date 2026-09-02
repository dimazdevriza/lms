@extends('layouts.lms')

@section('title', 'Detail Tugas & Pengumpulan')

@section('content')
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3 mb-3">
            @php
                $submittedSubmissions = $submittedSubmissions ?? $assignment->submissions;
                $allStudents = $allStudents ?? ($assignment->schoolClass?->students ?? $assignment->meeting?->schoolClass?->students ?? collect());
                $unsubmittedStudents = $unsubmittedStudents ?? collect();

                if (auth()->user()?->role === 'admin' && $assignment->meeting_id) {
                    $backUrl = route('admin.attendances.meetingAssignments', $assignment->meeting_id);
                } elseif ($assignment->meeting_id) {
                    $backUrl = route('guru.meetings.show', $assignment->meeting_id);
                } else {
                    $backUrl = route('guru.assignments.index');
                }
            @endphp
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ $backUrl }}" class="btn btn-outline-secondary-theme btn-sm" style="border-radius: var(--radius-sm);">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                    <h1 class="mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--text-heading);">📋 Detail Tugas & Pengumpulan</h1>
                </div>
                
                @if(auth()->user()->role === 'guru')
                <div class="d-flex gap-2">
                    <a href="{{ route('guru.assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-primary" style="border-radius: var(--radius-sm);">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <form action="{{ route('guru.assignments.destroy', $assignment) }}" method="POST" onsubmit="return confirm('Peringatan Keras: Menghapus tugas akan menghapus SELURUH data pengumpulan nilai dan file siswa terkait tugas ini! Apakah Anda sangat yakin ingin menghapusnya?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-trash me-1"></i> Hapus Tugas
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <div class="content-card" style="background: linear-gradient(135deg, rgba(27,94,32,0.03) 0%, rgba(67,160,71,0.01) 100%);">
            <div class="content-card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 style="color: var(--primary); font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $assignment->title }}</h4>
                        <p style="color: var(--text-muted);" class="mb-2">{{ $assignment->description }}</p>
                        <div class="d-flex gap-3 flex-wrap">
                            <span class="status-badge status-badge--hadir">{{ $assignment->schoolClass?->name ?? $assignment->meeting?->schoolClass?->name ?? '-' }}</span>
                            @if($assignment->isOnline())
                                <span class="status-badge" style="background: rgba(13,110,253,0.1); color: #0d6efd;"><i class="fas fa-laptop me-1"></i> Soal Online</span>
                            @elseif($assignment->type === 'external')
                                <span class="status-badge" style="background: rgba(25,135,84,0.1); color: var(--primary); font-weight: 700; border: none; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm);"><i class="fas fa-link me-1"></i> Kuis Online (Quizizz, dll)</span>
                            @else
                                <span class="status-badge" style="background: rgba(220,53,69,0.1); color: #dc3545;"><i class="fas fa-file-pdf me-1"></i> PDF</span>
                            @endif
                            <div class="d-flex align-items-center gap-2">
                                <span class="small" style="color: var(--text-muted);"><i class="fas fa-clock me-1"></i> Deadline: {{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->format('d M Y, H:i') : 'Tidak ada' }}</span>
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#extendDeadlineModal" style="border-radius: var(--radius-sm); padding: 0.15rem 0.6rem; font-size: 0.75rem;">
                                    <i class="fas fa-calendar-plus me-1 text-warning"></i> Perpanjang Waktu
                                </button>
                            </div>
                            @if($assignment->file_path)
                                <a href="{{ route('assignments.download', $assignment) }}" target="_blank" class="small fw-bold text-decoration-none" style="color: #dc3545;">
                                    <i class="fas fa-file-pdf me-1"></i> File Instruksi PDF
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="stat-card d-inline-block text-center" style="padding: 16px 24px;">
                            <div class="stat-value stat-value--primary" style="font-size: 1.75rem;">{{ $submittedSubmissions->count() }}{{ $allStudents->count() > 0 ? ' / ' . $allStudents->count() : '' }}</div>
                            <div class="stat-label">Siswa Mengumpulkan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($assignment->type === 'external')
        <div class="content-card mb-4" style="border-top: 4px solid var(--primary) !important;">
            <div class="content-card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-link text-success me-2"></i> Link Kuis Online</h5>
                    <p class="text-muted mb-0 small">Siswa diarahkan untuk mengerjakan kuis pada platform eksternal berikut.</p>
                    <a href="{{ $assignment->quiz_url }}" target="_blank" class="small text-primary d-inline-flex align-items-center gap-1 mt-2 fw-semibold" style="word-break: break-all;">
                        <i class="fas fa-external-link-alt"></i> {{ $assignment->quiz_url }}
                    </a>
                </div>
                <a href="{{ $assignment->quiz_url }}" target="_blank" class="btn btn-success px-4 py-2.5 fw-bold" style="border-radius: var(--radius-sm); border: none; background-color: var(--primary);">
                    <i class="fas fa-play me-2"></i> Kunjungi Situs Kuis
                </a>
            </div>
        </div>
    @endif

    @if($assignment->isOnline())
        {{-- Online Assignment: Show questions overview + per-student results --}}

        <!-- Questions Overview -->
        <div class="content-card mb-4">
            <div class="content-card-header">
                <div class="content-card-header-icon">
                    <i class="fas fa-list-ol"></i>
                </div>
                <h5 class="content-card-title mb-0">Daftar Soal ({{ $assignment->questions->count() }} soal, {{ $assignment->questions->sum('points') }} total poin)</h5>
            </div>
            <div class="content-card-body p-0">
                @foreach($assignment->questions as $question)
                    <div class="px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-secondary">{{ $question->order }}</span>
                                    @if($question->type === 'pilihan_ganda')
                                        <span class="status-badge" style="background: rgba(13,110,253,0.1); color: #0d6efd; font-size: 0.65rem;">Pilihan Ganda</span>
                                    @elseif($question->type === 'isian_singkat')
                                        <span class="status-badge" style="background: rgba(249,168,37,0.12); color: #B26A00; font-size: 0.65rem;">Isian Singkat</span>
                                    @else
                                        <span class="status-badge status-badge--hadir" style="font-size: 0.65rem;">Essay</span>
                                    @endif
                                    <span class="small" style="color: var(--text-muted);">({{ $question->points }} poin)</span>
                                </div>
                                <p class="mb-1">{{ $question->body }}</p>
                                @if($question->image)
                                    <div class="mb-2 text-start">
                                        <img src="{{ $question->image }}" class="img-fluid rounded border" style="max-height: 200px;">
                                    </div>
                                @endif
                                @if($question->type === 'pilihan_ganda')
                                    <div class="ms-2">
                                        @foreach($question->options as $opt)
                                            <small class="{{ $opt->is_correct ? 'fw-bold' : '' }}" style="color: {{ $opt->is_correct ? 'var(--secondary)' : 'var(--text-muted)' }};">
                                                {{ $opt->label }}. {{ $opt->body }}
                                                @if($opt->is_correct) ✓ @endif
                                            </small><br>
                                            @if($opt->image)
                                                <div class="mb-1 ms-3">
                                                    <img src="{{ $opt->image }}" class="img-fluid rounded border" style="max-height: 80px;">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @elseif($question->type === 'isian_singkat')
                                    <small style="color: var(--secondary);"><i class="fas fa-check me-1"></i>Jawaban: {{ $question->correct_answer }}</small>
                                @endif
                            </div>

                            {{-- Show answer stats if there are submissions --}}
                            @if($assignment->submissions->count() > 0 && $question->type !== 'essay')
                                @php
                                    $totalAnswers = 0;
                                    $correctAnswers = 0;
                                    foreach ($assignment->submissions as $sub) {
                                        $ans = $sub->questionAnswers->where('question_id', $question->id)->first();
                                        if ($ans) {
                                            $totalAnswers++;
                                            if ($ans->is_correct) $correctAnswers++;
                                        }
                                    }
                                    $pct = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100) : 0;
                                @endphp
                                <div class="text-center ms-3" style="min-width: 70px;">
                                    <div class="fw-bold" style="font-size: 1.1rem; font-family: 'Plus Jakarta Sans', sans-serif; color: {{ $pct >= 70 ? 'var(--secondary)' : ($pct >= 40 ? 'var(--accent)' : '#dc3545') }};">{{ $pct }}%</div>
                                    <small style="color: var(--text-muted); font-size: 0.65rem;">Benar</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="content-card">
            <div class="content-card-header">
                <div class="content-card-header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="content-card-title mb-0">Daftar Pengumpulan Siswa</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Waktu Kumpul</th>
                            @foreach($assignment->questions as $question)
                                <th class="text-center" title="{{ $question->body }}">
                                    <span class="badge bg-secondary">{{ $question->order }}</span>
                                </th>
                            @endforeach
                            <th class="text-center">Total</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submittedSubmissions as $submission)
                            <tr>
                                <td>
                                    <div><strong>{{ $submission->student?->user?->name ?? 'Siswa' }}</strong></div>
                                    <small style="color: var(--text-muted);">NISN: {{ $submission->student?->nisn ?? '-' }}</small>
                                </td>
                                <td>
                                    <small>{{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('d/m/Y H:i') : '-' }}</small>
                                    @if($assignment->due_at && $submission->submitted_at && \Carbon\Carbon::parse($submission->submitted_at)->gt($assignment->due_at))
                                        <span class="status-badge" style="background: rgba(220,53,69,0.1); color: #dc3545; font-size: 0.6rem;">Terlambat</span>
                                    @endif
                                </td>
                                @foreach($assignment->questions as $question)
                                    @php
                                        $ans = $submission->questionAnswers->where('question_id', $question->id)->first();
                                    @endphp
                                    <td class="text-center">
                                        @if($ans)
                                            @if($ans->is_correct === true)
                                                <span class="fw-bold" style="color: var(--secondary);" title="Benar: {{ $ans->score }}/{{ $question->points }}">✓</span>
                                            @elseif($ans->is_correct === false)
                                                <span class="text-danger fw-bold" title="Salah: {{ $ans->score }}/{{ $question->points }}">✗</span>
                                            @else
                                                <span style="color: var(--accent);" title="Belum dinilai">⏳</span>
                                            @endif
                                        @else
                                            <span style="color: var(--text-muted);">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center">
                                    @if($submission->score !== null)
                                        <span class="status-badge status-badge--hadir" style="font-size: 0.9rem;">{{ $submission->score }}</span>
                                    @else
                                        <span class="small" style="color: var(--text-muted);">Belum</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary-theme" data-bs-toggle="modal" data-bs-target="#detailModal{{ $submission->id }}" style="border-radius: var(--radius-sm);">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        @foreach($unsubmittedStudents as $student)
                            <tr class="table-light opacity-75">
                                <td>
                                    <div><strong>{{ $student->user?->name ?? 'Siswa' }}</strong></div>
                                    <small style="color: var(--text-muted);">NISN: {{ $student->nisn ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-medium" style="font-size: 0.75rem;">
                                        <i class="fas fa-times-circle me-1"></i>Belum Mengumpulkan
                                    </span>
                                </td>
                                @foreach($assignment->questions as $question)
                                    <td class="text-center"><span style="color: var(--text-muted);">-</span></td>
                                @endforeach
                                <td class="text-center"><span style="color: var(--text-muted);">-</span></td>
                                <td class="text-center"><span class="text-muted small">-</span></td>
                            </tr>
                        @endforeach

                        @if($allStudents->count() === 0)
                            <tr>
                                <td colspan="{{ 4 + $assignment->questions->count() }}" class="text-center py-5" style="color: var(--text-muted);">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                                        <div class="empty-state-text">Belum ada siswa di kelas ini.</div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    @push('modals')
    <!-- Detail Modals -->
    @foreach($assignment->submissions as $submission)
<!-- Detail Modal -->
                            <div class="modal fade" id="detailModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background: var(--primary); color: white;">
                                            <h5 class="modal-title text-white">📋 Jawaban: {{ $submission->student?->user?->name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @foreach($assignment->questions as $question)
                                                @php
                                                    $ans = $submission->questionAnswers->where('question_id', $question->id)->first();
                                                @endphp
                                                <div class="mb-4 p-3 border rounded {{ $ans && $ans->is_correct === true ? 'border-success' : ($ans && $ans->is_correct === false ? 'border-danger' : '') }}" style="border-radius: var(--radius-sm);">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <strong>Soal {{ $question->order }}.</strong> {{ $question->body }}
                                                            <span class="small" style="color: var(--text-muted);">({{ $question->points }} poin)</span>
                                                            @if($question->image)
                                                                <div class="mt-2 mb-2 text-start">
                                                                    <img src="{{ $question->image }}" class="img-fluid rounded border" style="max-height: 150px;">
                                                                </div>
                                                            @endif
                                                        </div>
                                                        @if($ans)
                                                            @if($ans->is_correct === true)
                                                                <span class="badge bg-success">✓ {{ $ans->score }}/{{ $question->points }}</span>
                                                            @elseif($ans->is_correct === false)
                                                                <span class="badge bg-danger">✗ {{ $ans->score ?? 0 }}/{{ $question->points }}</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">⏳ Belum dinilai</span>
                                                            @endif
                                                        @endif
                                                    </div>

                                                    @if($question->type === 'pilihan_ganda' && $ans)
                                                        <div class="ms-3">
                                                            @foreach($question->options as $opt)
                                                                <div class="small {{ $opt->is_correct ? 'fw-bold' : '' }} {{ $ans->selected_option_id == $opt->id && !$opt->is_correct ? 'text-danger fw-bold' : '' }}" style="{{ $opt->is_correct ? 'color: var(--secondary);' : '' }}">
                                                                    {{ $opt->label }}. {{ $opt->body }}
                                                                    @if($ans->selected_option_id == $opt->id) ← Jawaban siswa @endif
                                                                    @if($opt->is_correct) ✓ @endif
                                                                </div>
                                                                @if($opt->image)
                                                                    <div class="mt-1 mb-2 ms-3">
                                                                        <img src="{{ $opt->image }}" class="img-fluid rounded border" style="max-height: 80px;">
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @elseif($question->type === 'isian_singkat' && $ans)
                                                         <div class="ms-3 p-3 bg-light border rounded mb-3">
                                                             <div class="mb-2" style="font-size: 0.95rem;">
                                                                 <strong class="text-dark">Jawaban Siswa:</strong> 
                                                                 <span class="px-2 py-1 bg-white border rounded text-dark fw-bold ms-1" style="font-size: 0.95rem;">{{ $ans->answer_text }}</span>
                                                             </div>
                                                             <div style="font-size: 0.95rem;">
                                                                 <strong class="text-success">Jawaban Benar:</strong> 
                                                                 <span class="px-2 py-1 bg-white border rounded text-success fw-bold ms-1" style="font-size: 0.95rem;">{{ $question->correct_answer }}</span>
                                                             </div>
                                                         </div>
                                                     @elseif($question->type === 'essay' && $ans)
                                                         <div class="ms-3 mb-3">
                                                             <div class="fw-bold text-dark mb-2" style="font-size: 0.95rem;"><i class="fas fa-align-left me-1 text-primary"></i> Jawaban Siswa:</div>
                                                             <div class="p-3 bg-white border rounded text-dark mb-3" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6; border-radius: var(--radius-sm);">{{ $ans->answer_text }}</div>
                                                         </div>
                                                        <!-- Essay grading form -->
                                                        <form action="{{ route('guru.assignments.grade-question', $ans) }}" method="POST" class="essay-grading-form ms-3 p-3 rounded" style="background: var(--bg-body); border-radius: var(--radius-sm);">
                                                            @csrf
                                                            <div class="row g-2 align-items-end">
                                                                <div class="col-auto">
                                                                    <label class="form-label small fw-bold mb-1">Skor (0-{{ $question->points }})</label>
                                                                    <input type="number" name="score" class="form-control form-control-sm" value="{{ $ans->score ?? '' }}" min="0" max="{{ $question->points }}" required style="width: 80px;">
                                                                </div>
                                                                <div class="col-auto">
                                                                    <label class="form-label small fw-bold mb-1">Benar?</label>
                                                                    <select name="is_correct" class="form-select form-select-sm" required>
                                                                        <option value="1" {{ $ans->is_correct === true ? 'selected' : '' }}>Ya</option>
                                                                        <option value="0" {{ $ans->is_correct === false ? 'selected' : '' }}>Tidak</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-auto">
                                                                    <button type="submit" class="btn btn-sm btn-outline-secondary-theme"><i class="fas fa-check me-1"></i>Simpan</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    @endif
                                            @endforeach

                                            <!-- Private Comments Section -->
                                            <div class="border-top mt-4 pt-3 text-start">
                                                <h6 class="fw-bold mb-3"><i class="fas fa-comment-dots text-primary me-2"></i> Komentar Pribadi</h6>
                                                
                                                <!-- Comments List -->
                                                <div class="d-flex flex-column gap-2 mb-3" style="max-height: 250px; overflow-y: auto;">
                                                    @forelse($submission->comments as $comment)
                                                        <div class="p-2 rounded-3 text-start" style="background: {{ $comment->user_id === Auth::id() ? 'rgba(27, 94, 32, 0.04)' : '#f8f9fa' }}; border: 1px solid rgba(0,0,0,0.03); font-size: 0.85rem;">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="fw-bold text-dark">{{ $comment->user->name }}</span>
                                                                <small class="text-muted" style="font-size: 0.7rem;">{{ $comment->created_at->diffForHumans() }}</small>
                                                            </div>
                                                            <div class="text-muted" style="white-space: pre-wrap;">{{ $comment->content }}</div>
                                                        </div>
                                                    @empty
                                                        <p class="text-muted small text-center my-2">Belum ada komentar pribadi.</p>
                                                    @endforelse
                                                </div>

                                                <!-- Add Comment Form -->
                                                <form action="{{ route('submissions.comments.store', $submission->id) }}" method="POST">
                                                    @csrf
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="content" class="form-control bg-light border-0 py-2 px-3" 
                                                               placeholder="Tulis balasan komentar..." required style="border-radius: var(--radius-sm) 0 0 var(--radius-sm);">
                                                        <button class="btn btn-outline-primary-theme" type="submit" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
    @endforeach
    @endpush
    @else
        {{-- PDF Assignment: Original table --}}
        <div class="content-card">
            <div class="content-card-header">
                <div class="content-card-header-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="content-card-title mb-0">Daftar Pengumpulan Siswa</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Waktu Kumpul</th>
                            <th>Jawaban Teks</th>
                            <th>File Lampiran / Audio</th>
                            <th class="text-center">Nilai</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($submittedSubmissions as $submission)
                            <tr>
                                <td>
                                    <div><strong>{{ $submission->student?->user?->name ?? 'Siswa' }}</strong></div>
                                    <small style="color: var(--text-muted);">NISN: {{ $submission->student?->nisn ?? '-' }}</small>
                                </td>
                                <td>
                                    <small>{{ $submission->submitted_at ? \Carbon\Carbon::parse($submission->submitted_at)->format('d/m/Y H:i') : '-' }}</small>
                                    @if($assignment->due_at && $submission->submitted_at && \Carbon\Carbon::parse($submission->submitted_at)->gt($assignment->due_at))
                                        <span class="status-badge" style="background: rgba(220,53,69,0.1); color: #dc3545; font-size: 0.6rem;">Terlambat</span>
                                    @endif
                                </td>
                                <td>
                                    <span title="{{ $submission->answer_text }}">
                                        {{ Str::limit($submission->answer_text, 50) ?: '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($submission->file_path)
                                        @php
                                            $subExtension = pathinfo($submission->file_path, PATHINFO_EXTENSION);
                                            $isAudio = in_array(strtolower($subExtension), ['mp3', 'm4a', 'wav', 'webm', 'ogg', 'aac', 'flac', '3gp', 'opus', 'wma']);
                                        @endphp
                                        @if($isAudio)
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#gradeModal{{ $submission->id }}" style="border-radius: var(--radius-sm);">
                                                <i class="fas fa-microphone-alt me-1"></i> Rekaman Suara
                                            </button>
                                        @else
                                            <a href="{{ route('submissions.download', $submission) }}" target="_blank" class="btn btn-sm btn-outline-danger" style="border-radius: var(--radius-sm);">
                                                <i class="fas fa-file-pdf"></i> Lihat File
                                            </a>
                                        @endif
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($submission->score !== null)
                                        <span class="status-badge status-badge--hadir" style="font-size: 0.9rem;">{{ $submission->score }}</span>
                                    @else
                                        <span class="small" style="color: var(--text-muted);">Belum dinilai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary-theme" data-bs-toggle="modal" data-bs-target="#gradeModal{{ $submission->id }}" style="border-radius: var(--radius-sm);">
                                        <i class="fas fa-check-circle me-1"></i> Nilai
                                    </button>
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        @foreach($unsubmittedStudents as $student)
                            <tr class="table-light opacity-75">
                                <td>
                                    <div><strong>{{ $student->user?->name ?? 'Siswa' }}</strong></div>
                                    <small style="color: var(--text-muted);">NISN: {{ $student->nisn ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-medium" style="font-size: 0.75rem;">
                                        <i class="fas fa-times-circle me-1"></i>Belum Mengumpulkan
                                    </span>
                                </td>
                                <td><span style="color: var(--text-muted);">-</span></td>
                                <td><span style="color: var(--text-muted);">-</span></td>
                                <td class="text-center"><span style="color: var(--text-muted);">-</span></td>
                                <td class="text-center"><span class="text-muted small">-</span></td>
                            </tr>
                        @endforeach

                        @if($allStudents->count() === 0)
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
                                        <div class="empty-state-text">Belum ada siswa di kelas ini.</div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    @push('modals')
    <!-- Grade Modals -->
    @foreach($assignment->submissions as $submission)
<div class="modal fade" id="gradeModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
                                         <div class="modal-dialog">
                                             <div class="modal-content">
                                                 <div class="modal-header" style="background: var(--primary); color: white;">
                                                     <h5 class="modal-title text-white">Beri Nilai: {{ $submission->student?->user?->name }}</h5>
                                                     <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                 </div>
                                                 <div class="modal-body text-start">
                                                     @if($submission->answer_text)
                                                         <div class="mb-4">
                                                             <label class="form-label fw-bold text-dark"><i class="fas fa-pen-fancy me-1 text-primary"></i> Jawaban Teks Siswa:</label>
                                                             <div class="p-3 bg-light border rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">{{ $submission->answer_text }}</div>
                                                         </div>
                                                     @endif

                                                     @if($submission->file_path)
                                                         @php
                                                             $subExtension = pathinfo($submission->file_path, PATHINFO_EXTENSION);
                                                             $isAudio = in_array(strtolower($subExtension), ['mp3', 'm4a', 'wav', 'webm', 'ogg', 'aac', 'flac', '3gp', 'opus', 'wma']);
                                                         @endphp
                                                         @if($isAudio)
                                                             <div class="mb-4 p-3 rounded border" style="background: linear-gradient(135deg, rgba(27,94,32,0.06), rgba(67,160,71,0.02)); border-color: rgba(27,94,32,0.2) !important; border-radius: var(--radius-md) !important;">
                                                                 <div class="d-flex align-items-center justify-content-between mb-2">
                                                                     <div class="d-flex align-items-center gap-2">
                                                                         <i class="fas fa-microphone-alt text-success fs-5"></i>
                                                                         <h6 class="fw-bold mb-0 text-dark">Rekaman Suara Siswa ({{ strtoupper($subExtension) }})</h6>
                                                                     </div>
                                                                     <a href="{{ route('submissions.download', $submission) }}" download class="btn btn-sm btn-outline-success">
                                                                         <i class="fas fa-download me-1"></i> Unduh Audio
                                                                     </a>
                                                                 </div>
                                                                 <audio controls class="w-100 my-2">
                                                                     <source src="{{ route('submissions.download', $submission) }}">
                                                                     Browser Anda tidak mendukung pemutaran audio.
                                                                 </audio>
                                                             </div>
                                                         @else
                                                             <div class="mb-4 p-3 rounded border bg-light d-flex align-items-center justify-content-between">
                                                                 <div>
                                                                     <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-pdf text-danger me-2"></i> File Lampiran Siswa ({{ strtoupper($subExtension) }})</h6>
                                                                 </div>
                                                                 <a href="{{ route('submissions.download', $submission) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                                                     <i class="fas fa-external-link-alt me-1"></i> Buka / Unduh File
                                                                 </a>
                                                             </div>
                                                         @endif
                                                     @endif

                                                     <form action="{{ route('guru.assignments.grade-submission', $submission) }}" method="POST" class="mb-3">
                                                         @csrf
                                                         <div class="mb-3">
                                                             <label class="form-label fw-semibold" style="color: var(--primary);">Skor (0-100)</label>
                                                             <input type="number" name="score" class="form-control" value="{{ $submission->score }}" min="0" max="100" required>
                                                         </div>
                                                         <div class="mb-3">
                                                             <label class="form-label fw-semibold" style="color: var(--primary);">Catatan Guru</label>
                                                             <textarea name="feedback" class="form-control" rows="3">{{ $submission->feedback }}</textarea>
                                                         </div>
                                                         <div class="text-end">
                                                             <button type="submit" class="btn btn-outline-secondary-theme">Simpan Nilai</button>
                                                         </div>
                                                     </form>

                                                     <!-- Private Comments Section -->
                                                     <div class="border-top mt-4 pt-3">
                                                         <h6 class="fw-bold mb-3"><i class="fas fa-comment-dots text-primary me-2"></i> Komentar Pribadi</h6>
                                                         
                                                         <!-- Comments List -->
                                                         <div class="d-flex flex-column gap-2 mb-3" style="max-height: 200px; overflow-y: auto;">
                                                             @forelse($submission->comments as $comment)
                                                                 <div class="p-2 rounded-3 text-start" style="background: {{ $comment->user_id === Auth::id() ? 'rgba(27, 94, 32, 0.04)' : '#f8f9fa' }}; border: 1px solid rgba(0,0,0,0.03); font-size: 0.85rem;">
                                                                     <div class="d-flex justify-content-between align-items-center mb-1">
                                                                         <span class="fw-bold text-dark">{{ $comment->user->name }}</span>
                                                                         <small class="text-muted" style="font-size: 0.7rem;">{{ $comment->created_at->diffForHumans() }}</small>
                                                                     </div>
                                                                     <div class="text-muted" style="white-space: pre-wrap;">{{ $comment->content }}</div>
                                                                 </div>
                                                             @empty
                                                                 <p class="text-muted small text-center my-2">Belum ada komentar pribadi.</p>
                                                             @endforelse
                                                         </div>

                                                         <!-- Add Comment Form -->
                                                         <form action="{{ route('submissions.comments.store', $submission->id) }}" method="POST">
                                                             @csrf
                                                             <div class="input-group input-group-sm">
                                                                 <input type="text" name="content" class="form-control bg-light border-0 py-2 px-3" 
                                                                        placeholder="Tulis balasan komentar..." required style="border-radius: var(--radius-sm) 0 0 var(--radius-sm);">
                                                                 <button class="btn btn-outline-primary-theme" type="submit" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                                                                     <i class="fas fa-paper-plane"></i>
                                                                 </button>
                                                             </div>
                                                         </form>
                                                     </div>
                                                 </div>
                                                 <div class="modal-footer">
                                                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
    @endforeach
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept essay grading form submission
            document.querySelectorAll('.essay-grading-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalBtnHtml = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                        
                        if (data.success) {
                            // ponytail: show a clean green alert next to the form
                            let alertDiv = form.querySelector('.grading-alert');
                            if (!alertDiv) {
                                alertDiv = document.createElement('div');
                                alertDiv.className = 'grading-alert alert alert-success py-1 px-2 mt-2 mb-0 small border-0 text-success';
                                alertDiv.style.borderRadius = 'var(--radius-sm)';
                                alertDiv.style.fontSize = '0.75rem';
                                form.appendChild(alertDiv);
                            }
                            alertDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + data.message;
                            alertDiv.style.display = 'block';
                            
                            setTimeout(() => {
                                alertDiv.style.display = 'none';
                            }, 3000);
                        } else {
                            alert('Error: ' + (data.message || 'Gagal menyimpan nilai.'));
                        }
                    })
                    .catch(err => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                        console.error(err);
                        alert('Terjadi kesalahan jaringan.');
                    });
                });
            });
        });
    </script>
    <!-- Extend Deadline Modal -->
    <div class="modal fade" id="extendDeadlineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary); color: white;">
                    <h5 class="modal-title text-white"><i class="fas fa-clock me-2"></i>Perpanjang Batas Waktu Tugas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('guru.assignments.extend-deadline', $assignment) }}" method="POST">
                    @csrf
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--primary);">Judul Tugas</label>
                            <input type="text" class="form-control bg-light" value="{{ $assignment->title }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="color: var(--primary);">Batas Waktu Baru <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="due_at_input" name="due_at" class="form-control" value="{{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->format('Y-m-d\TH:i') : '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Tambah Cepat (Presets):</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addTimePreset(1)">+1 Hari</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addTimePreset(3)">+3 Hari</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addTimePreset(7)">+1 Minggu</button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-outline-secondary-theme"><i class="fas fa-save me-1"></i> Simpan Batas Waktu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function addTimePreset(days) {
            const input = document.getElementById('due_at_input');
            let baseDate = input.value ? new Date(input.value) : new Date();
            baseDate.setDate(baseDate.getDate() + days);
            
            const pad = (num) => String(num).padStart(2, '0');
            const formatted = baseDate.getFullYear() + '-' +
                pad(baseDate.getMonth() + 1) + '-' +
                pad(baseDate.getDate()) + 'T' +
                pad(baseDate.getHours()) + ':' +
                pad(baseDate.getMinutes());
            
            input.value = formatted;
        }
    </script>
    @endpush
    @endif
@endsection
