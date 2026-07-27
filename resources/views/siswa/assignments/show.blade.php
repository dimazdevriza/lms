@extends('layouts.lms')

@section('title', $assignment->title)

@section('content')
    <!-- Header -->
    <div class="mb-4 reveal">
        <div class="d-flex align-items-center gap-3 mb-3">
            <a href="{{ route('siswa.assignments.index') }}" class="btn btn-outline-secondary-theme btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <h1 class="h3 mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $submission ? '📊 Hasil Tugas' : '📝 Kerjakan Tugas' }}</h1>
        </div>

        @php
            $isDeadlinePassed = $assignment->due_at && \Carbon\Carbon::parse($assignment->due_at)->isPast();
        @endphp

        <div class="content-card" style="border-top: none;">
            <div class="content-card-body pt-4">
                <h4 style="color: var(--primary); font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $assignment->title }}</h4>
                @if($assignment->description)
                    <p class="text-muted mb-2">{{ $assignment->description }}</p>
                @endif
                <div class="d-flex gap-2 flex-wrap">
                    @if($assignment->isOnline())
                        <span class="status-badge status-badge--online"><i class="fas fa-laptop me-1"></i> Soal Online</span>
                        <span class="text-muted small"><i class="fas fa-list-ol me-1"></i> {{ $assignment->questions->count() }} soal</span>
                        <span class="text-muted small"><i class="fas fa-star me-1"></i> {{ $assignment->questions->sum('points') }} total poin</span>
                    @elseif($assignment->type === 'external')
                        <span class="status-badge status-badge--hadir" style="background: rgba(25,135,84,0.1); color: var(--primary); border: none; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-weight: 700;"><i class="fas fa-link me-1"></i> Kuis Online (Quizizz, dll)</span>
                    @else
                        <span class="status-badge status-badge--alpa"><i class="fas fa-file-pdf me-1"></i> Tugas PDF</span>
                    @endif
                    @if($assignment->due_at)
                        <span class="small {{ $isDeadlinePassed ? 'text-danger fw-bold' : 'text-muted' }}">
                            <i class="fas fa-clock me-1"></i> Deadline: {{ \Carbon\Carbon::parse($assignment->due_at)->format('d M Y, H:i') }}
                            @if($isDeadlinePassed)
                                <span class="status-badge status-badge--alpa ms-1">Terlewat</span>
                            @else
                                ({{ \Carbon\Carbon::parse($assignment->due_at)->diffForHumans() }})
                            @endif
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            @foreach($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($submission)
        {{-- Already submitted: show results --}}
        <div class="content-card mb-4 reveal reveal-delay-1">
            <div class="content-card-body text-center py-4">
                <h5 style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;">✅ Tugas sudah dikumpulkan</h5>
                <p class="text-muted mb-2">Dikumpulkan pada {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y, H:i') }}</p>
                @if($submission->score !== null)
                    <div class="d-inline-block rounded-3 p-3 mt-2" style="background: rgba(27, 94, 32, 0.04);">
                        <h2 class="mb-0" style="color: var(--primary); font-weight: 800; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $submission->score }}</h2>
                        <small class="text-muted text-uppercase fw-bold">Nilai</small>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center gap-2 mt-2">
                        <span class="status-badge status-badge--pending fs-6"><i class="fas fa-hourglass-half me-1"></i> Menunggu penilaian guru</span>
                        @if(!$isDeadlinePassed)
                            <div class="d-flex gap-2 align-items-center mt-2 flex-wrap justify-content-center">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" type="button" data-bs-toggle="collapse" data-bs-target="#editSubmissionCollapse" aria-expanded="{{ ($errors->any() || request()->has('edit')) ? 'true' : 'false' }}" aria-controls="editSubmissionCollapse">
                                    <i class="fas fa-edit me-1"></i> Edit Jawaban
                                </button>
                                <form action="{{ route('siswa.assignments.unsubmit', $assignment) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengiriman tugas ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                        <i class="fas fa-undo me-1"></i> Batalkan Pengiriman
                                    </button>
                                </form>
                            </div>
                        @else
                            <small class="text-danger mt-1"><i class="fas fa-lock me-1"></i> Batas waktu pengumpulan telah berakhir. Tugas tidak dapat diubah lagi.</small>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($submission->score === null && !$isDeadlinePassed)
            <div class="collapse {{ ($errors->any() || request()->has('edit')) ? 'show' : '' }} mb-4" id="editSubmissionCollapse">
                <div class="card border-0 shadow-sm p-4" style="border-radius: var(--radius-md) !important; border-top: 4px solid var(--primary) !important;">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <h5 class="fw-bold text-dark mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-edit text-primary me-2"></i> Edit Jawaban Tugas</h5>
                        <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i> Dapat diubah sebelum deadline</span>
                    </div>

                    @if($assignment->isOnline())
                        <form method="POST" action="{{ route('siswa.assignments.submit', $assignment) }}" id="editQuizForm">
                            @csrf
                            @foreach($assignment->questions as $question)
                                @php $ans = $answers[$question->id] ?? null; @endphp
                                <div class="content-card mb-3 question-card-student" style="border-left: 4px solid var(--primary);">
                                    <div class="content-card-body">
                                        <div class="d-flex align-items-start gap-2 mb-3">
                                            <span class="badge bg-secondary">{{ $question->order }}</span>
                                            @if($question->type === 'pilihan_ganda')
                                                <span class="status-badge status-badge--online" style="font-size: 0.65rem;">Pilihan Ganda</span>
                                            @elseif($question->type === 'isian_singkat')
                                                <span class="status-badge status-badge--pending" style="font-size: 0.65rem;">Isian Singkat</span>
                                            @else
                                                <span class="status-badge status-badge--hadir" style="font-size: 0.65rem;">Essay</span>
                                            @endif
                                            <span class="text-muted small">({{ $question->points }} poin)</span>
                                        </div>

                                        <p class="fw-bold mb-3">{{ $question->body }}</p>
                                        @if($question->image)
                                            <div class="mb-3 text-start">
                                                <img src="{{ $question->image }}" class="img-fluid rounded border" style="max-height: 250px;">
                                            </div>
                                        @endif

                                        @if($question->type === 'pilihan_ganda')
                                            @foreach($question->options as $opt)
                                                @php
                                                    $selectedOptId = old("answers.{$question->id}.selected_option_id", $ans?->selected_option_id);
                                                @endphp
                                                <div class="form-check mb-2 p-2 rounded border option-select">
                                                    <input class="form-check-input" type="radio" 
                                                           name="answers[{{ $question->id }}][selected_option_id]" 
                                                           value="{{ $opt->id }}" 
                                                           id="edit_opt_{{ $opt->id }}"
                                                           {{ $selectedOptId == $opt->id ? 'checked' : '' }}>
                                                    <label class="form-check-label w-100" for="edit_opt_{{ $opt->id }}" style="cursor: pointer;">
                                                        <strong class="me-2">{{ $opt->label }}.</strong> {{ $opt->body }}
                                                        @if($opt->image)
                                                            <div class="mt-1">
                                                                <img src="{{ $opt->image }}" class="img-fluid rounded border" style="max-height: 120px;">
                                                            </div>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        @elseif($question->type === 'isian_singkat')
                                            <input type="text" 
                                                   name="answers[{{ $question->id }}][answer_text]" 
                                                   class="form-control" 
                                                   placeholder="Ketik jawaban singkat Anda..." 
                                                   value="{{ old("answers.{$question->id}.answer_text", $ans?->answer_text) }}"
                                                   required>
                                        @elseif($question->type === 'essay')
                                            <textarea name="answers[{{ $question->id }}][answer_text]" 
                                                      class="form-control" 
                                                      rows="4" 
                                                      placeholder="Tuliskan jawaban essay Anda di sini..."
                                                      required>{{ old("answers.{$question->id}.answer_text", $ans?->answer_text) }}</textarea>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-toggle="collapse" data-bs-target="#editSubmissionCollapse">Batal</button>
                                <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-1"></i> Simpan Perubahan Jawaban</button>
                            </div>
                        </form>
                    @else
                        <form method="POST" action="{{ route('siswa.assignments.submit', $assignment) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jawaban Teks (Opsional)</label>
                                <textarea name="answer_text" class="form-control" rows="4" placeholder="Tuliskan catatan atau jawaban singkat Anda di sini...">{{ old('answer_text', $submission->answer_text) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload File Baru (Opsional, untuk mengganti file lama)</label>
                                <input type="file" class="form-control" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" onchange="validateFileSize(this)">
                                <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle me-1"></i> Maksimal ukuran file: 10 MB. Biarkan kosong jika tidak ingin mengubah file.</small>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-toggle="collapse" data-bs-target="#editSubmissionCollapse">Batal</button>
                                <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-save me-1"></i> Simpan Perubahan Tugas</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <!-- Private Comments Section -->
        <div class="card border-0 shadow-sm mb-4 reveal reveal-delay-1" style="border-radius: var(--radius-md) !important;">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-comment-dots text-primary me-2"></i> Komentar Pribadi</h5>
                <small class="text-muted">Komentar hanya dapat dilihat oleh Anda dan guru pengampu.</small>
            </div>
            <div class="card-body p-4">
                <!-- Comments List -->
                <div class="d-flex flex-column gap-3 mb-4">
                    @forelse($submission->comments as $comment)
                        <div class="p-3 rounded-3" style="background: {{ $comment->user_id === Auth::id() ? 'rgba(27, 94, 32, 0.04)' : '#f8f9fa' }}; border: 1px solid rgba(0,0,0,0.03);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $comment->user->name }}</span>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $comment->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="text-muted" style="font-size: 0.85rem; white-space: pre-wrap;">{{ $comment->content }}</div>
                        </div>
                    @empty
                        <p class="text-muted small text-center my-3">Belum ada komentar pribadi.</p>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                <form action="{{ route('submissions.comments.store', $submission->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="content" class="form-control bg-light border-0 py-2 px-3" 
                               placeholder="Kirim komentar pribadi ke guru..." required style="border-radius: var(--radius-sm) 0 0 var(--radius-sm); font-size: 0.9rem;">
                        <button class="btn btn-outline-primary-theme" type="submit" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Show answers with results --}}
        @if($assignment->isOnline())
            @foreach($assignment->questions as $question)
                @php
                    $ans = $answers[$question->id] ?? null;
                @endphp
                <div class="content-card mb-3 reveal reveal-delay-{{ min($loop->index + 2, 5) }}" style="{{ $ans && $ans->is_correct === true ? 'border-left: 4px solid var(--secondary);' : ($ans && $ans->is_correct === false ? 'border-left: 4px solid #C62828;' : '') }}">
                    <div class="content-card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-secondary me-1">{{ $question->order }}</span>
                                @if($question->type === 'pilihan_ganda')
                                    <span class="status-badge status-badge--online" style="font-size: 0.65rem;">Pilihan Ganda</span>
                                @elseif($question->type === 'isian_singkat')
                                    <span class="status-badge status-badge--pending" style="font-size: 0.65rem;">Isian Singkat</span>
                                @else
                                    <span class="status-badge status-badge--hadir" style="font-size: 0.65rem;">Essay</span>
                                @endif
                                <span class="text-muted small ms-1">({{ $question->points }} poin)</span>
                            </div>
                            @if($ans)
                                @if($ans->is_correct === true)
                                    <span class="status-badge status-badge--hadir">✓ Benar ({{ $ans->score }}/{{ $question->points }})</span>
                                @elseif($ans->is_correct === false)
                                    <span class="status-badge status-badge--alpa">✗ Salah ({{ $ans->score ?? 0 }}/{{ $question->points }})</span>
                                @else
                                    <span class="status-badge status-badge--pending">⏳ Belum dinilai</span>
                                @endif
                            @endif
                        </div>
                        
                        <p class="mb-3 fw-bold">{{ $question->body }}</p>
                        @if($question->image)
                            <div class="mb-3 text-start">
                                <img src="{{ $question->image }}" class="img-fluid rounded border" style="max-height: 250px;">
                            </div>
                        @endif

                        @if($question->type === 'pilihan_ganda')
                            @foreach($question->options as $opt)
                                <div class="d-flex align-items-center gap-2 p-2 rounded mb-1 
                                    {{ $opt->is_correct ? 'bg-success bg-opacity-10' : '' }}
                                    {{ $ans && $ans->selected_option_id == $opt->id && !$opt->is_correct ? 'bg-danger bg-opacity-10' : '' }}">
                                    <span class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" 
                                          style="width: 28px; height: 28px; font-size: 0.8rem; font-weight: 700;
                                          {{ $opt->is_correct ? 'background: var(--secondary); color: white;' : ($ans && $ans->selected_option_id == $opt->id ? 'background: #dc3545; color: white;' : 'background: #e2e8f0;') }}">
                                        {{ $opt->label }}
                                    </span>
                                    <span class="{{ $opt->is_correct ? 'text-success fw-bold' : '' }} {{ $ans && $ans->selected_option_id == $opt->id && !$opt->is_correct ? 'text-danger' : '' }}">
                                        {{ $opt->body }}
                                        @if($opt->image)
                                            <div class="mt-1">
                                                <img src="{{ $opt->image }}" class="img-fluid rounded border" style="max-height: 120px;">
                                            </div>
                                        @endif
                                    </span>
                                    @if($ans && $ans->selected_option_id == $opt->id)
                                        <small class="text-muted ms-auto">(Jawaban Anda)</small>
                                    @endif
                                </div>
                            @endforeach
                        @elseif($question->type === 'isian_singkat' && $ans)
                            <div class="ms-2">
                                <div class="small mb-1"><strong>Jawaban Anda:</strong> <span class="{{ $ans->is_correct ? 'text-success' : 'text-danger' }}">{{ $ans->answer_text }}</span></div>
                                @if(!$ans->is_correct)
                                    <div class="small text-success"><strong>Jawaban benar:</strong> {{ $question->correct_answer }}</div>
                                @endif
                            </div>
                        @elseif($question->type === 'essay' && $ans)
                            <div class="ms-2">
                                <div class="p-3 rounded small" style="background: rgba(27, 94, 32, 0.03);">{{ $ans->answer_text }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            {{-- PDF Assignment Submission Details --}}
            <div class="content-card mb-4 reveal reveal-delay-2">
                <div class="content-card-body">
                    <h5 class="fw-bold text-dark mb-4" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-folder-open text-primary me-2"></i> Jawaban Yang Dikirim</h5>
                    
                    @if($submission->answer_text)
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark"><i class="fas fa-align-left text-primary me-1"></i> Jawaban Teks Anda:</label>
                            <div class="p-3 bg-light border rounded text-dark" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6; border-radius: var(--radius-sm);">{{ $submission->answer_text }}</div>
                        </div>
                    @endif

                    @if($submission->file_path)
                        @php
                            $subIsPdf = Str::endsWith(strtolower($submission->file_path), '.pdf');
                            $subExtension = pathinfo($submission->file_path, PATHINFO_EXTENSION);
                            $subIcon = match(strtolower($subExtension)) {
                                'pdf' => 'fa-file-pdf text-danger',
                                'doc', 'docx' => 'fa-file-word text-primary',
                                'xls', 'xlsx' => 'fa-file-excel text-success',
                                'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                                default => 'fa-file-alt text-secondary'
                            };
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark"><i class="fas {{ $subIcon }} me-1"></i> File Lampiran Anda ({{ strtoupper($subExtension) }}):</label>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <a href="{{ route('submissions.download', $submission) }}" target="_blank" class="btn btn-sm btn-outline-secondary-theme">
                                    <i class="fas fa-download me-1"></i> Unduh File
                                </a>
                            </div>
                            @if($subIsPdf)
                                <div class="rounded border" style="overflow: hidden; height: 500px; background: #f8f9fa;">
                                    <iframe src="{{ route('submissions.download', $submission) }}" width="100%" height="100%" frameborder="0"></iframe>
                                </div>
                            @else
                                <div class="card p-4 border text-center shadow-sm" style="border-radius: var(--radius-md) !important; background: rgba(27, 94, 32, 0.015); border-color: rgba(27, 94, 32, 0.08) !important;">
                                    <i class="fas {{ $subIcon }} fa-3x mb-3"></i>
                                    <h6 class="fw-bold text-dark mb-1">Dokumen Lampiran ({{ strtoupper($subExtension) }})</h6>
                                    <p class="text-muted small mb-0">File ini tidak dapat ditampilkan langsung di browser. Silakan unduh untuk membukanya.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @elseif($isDeadlinePassed)
        {{-- Deadline passed and not submitted --}}
        <div class="content-card mb-4 reveal reveal-delay-1">
            <div class="content-card-body text-center py-5">
                <i class="fas fa-lock fa-3x mb-3" style="color: #C62828;"></i>
                <h5 style="color: #C62828;">⛔ Deadline Terlewat</h5>
                <p class="text-muted mb-0">
                    Batas waktu pengumpulan tugas ini sudah lewat pada 
                    <strong>{{ \Carbon\Carbon::parse($assignment->due_at)->format('d M Y, H:i') }}</strong>.<br>
                    Anda tidak dapat mengerjakan tugas ini lagi.
                </p>
            </div>
        </div>
    @else
        {{-- Not yet submitted: show answer form --}}
        @if($assignment->isOnline())
            <form method="POST" action="{{ route('siswa.assignments.submit', $assignment) }}" id="quizForm">
                @csrf

                @foreach($assignment->questions as $question)
                    <div class="content-card mb-3 question-card-student reveal reveal-delay-{{ min($loop->index + 1, 5) }}" id="question-card-{{ $question->id }}" style="border-left: 4px solid var(--primary);">
                        <div class="content-card-body">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <span class="badge bg-secondary">{{ $question->order }}</span>
                                @if($question->type === 'pilihan_ganda')
                                    <span class="status-badge status-badge--online" style="font-size: 0.65rem;">Pilihan Ganda</span>
                                @elseif($question->type === 'isian_singkat')
                                    <span class="status-badge status-badge--pending" style="font-size: 0.65rem;">Isian Singkat</span>
                                @else
                                    <span class="status-badge status-badge--hadir" style="font-size: 0.65rem;">Essay</span>
                                @endif
                                <span class="text-muted small">({{ $question->points }} poin)</span>
                            </div>

                            <p class="fw-bold mb-3">{{ $question->body }}</p>
                            @if($question->image)
                                <div class="mb-3 text-start">
                                    <img src="{{ $question->image }}" class="img-fluid rounded border" style="max-height: 250px;">
                                </div>
                            @endif

                            @if($question->type === 'pilihan_ganda')
                                @foreach($question->options as $opt)
                                    <div class="form-check mb-2 p-2 rounded border option-select">
                                        <input class="form-check-input" type="radio" 
                                               name="answers[{{ $question->id }}][selected_option_id]" 
                                               value="{{ $opt->id }}" 
                                               id="opt_{{ $opt->id }}"
                                               {{ old("answers.{$question->id}.selected_option_id") == $opt->id ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="opt_{{ $opt->id }}" style="cursor: pointer;">
                                            <strong class="me-2">{{ $opt->label }}.</strong> {{ $opt->body }}
                                            @if($opt->image)
                                                <div class="mt-1">
                                                    <img src="{{ $opt->image }}" class="img-fluid rounded border" style="max-height: 120px;">
                                                </div>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            @elseif($question->type === 'isian_singkat')
                                <input type="text" class="form-control" 
                                       name="answers[{{ $question->id }}][answer_text]"
                                       placeholder="Ketik jawaban Anda..." 
                                       value="{{ old("answers.{$question->id}.answer_text") }}">
                            @elseif($question->type === 'essay')
                                <textarea class="form-control" 
                                          name="answers[{{ $question->id }}][answer_text]"
                                          placeholder="Tulis jawaban essay Anda..." 
                                          rows="4">{{ old("answers.{$question->id}.answer_text") }}</textarea>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Submit Button -->
                <div class="content-card reveal reveal-delay-5">
                    <div class="content-card-body text-center py-4">
                        <p class="text-muted mb-3"><i class="fas fa-info-circle me-1"></i> Pastikan semua soal sudah dijawab sebelum mengirim.</p>
                        <button type="submit" class="btn btn-primary btn-lg" 
                                onclick="return confirm('Kirim jawaban? Jawaban tidak dapat diubah setelah dikirim.')">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Jawaban
                        </button>
                    </div>
                </div>
            </form>
        @elseif($assignment->type === 'external')
            {{-- External Quiz Section --}}
            <div class="content-card mb-4 reveal reveal-delay-1 text-center py-5" style="border-top: 4px solid var(--primary) !important;">
                <div class="content-card-body">
                    <i class="fas fa-gamepad fa-4x mb-3 text-success"></i>
                    <h4 class="fw-bold mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Kuis Online Eksternal</h4>
                    <p class="text-muted mb-4">Tugas ini diselenggarakan di platform kuis online luar (seperti Quizizz, Kahoot, dll). Klik tombol di bawah untuk mulai mengerjakan.</p>
                    <a href="{{ $assignment->quiz_url }}" target="_blank" class="btn btn-success btn-lg px-5 py-3 fw-bold rounded-pill shadow-lg d-inline-flex align-items-center gap-2">
                        <i class="fas fa-external-link-alt"></i> Mulaikan Kuis Online (Quizizz)
                    </a>
                </div>
            </div>

            <div class="content-card mb-4 reveal reveal-delay-2">
                <div class="content-card-body">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-paper-plane text-primary me-2"></i> Laporkan Hasil / Kirim Bukti</h5>
                    <form method="POST" action="{{ route('siswa.assignments.submit', $assignment) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Keterangan / Laporan (Opsional)</label>
                            <textarea class="form-control" name="answer_text" placeholder="Tuliskan skor Anda atau catatan lainnya jika diperlukan..." rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload Bukti Dokumen (Opsional)</label>
                            <input type="file" class="form-control" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" onchange="validateFileSize(this)">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle text-primary me-1"></i> Unggah file bukti (PDF, Word, Excel, PPT). <strong class="text-dark">Maksimal 10 MB</strong>.</small>
                        </div>
                        <button class="btn btn-primary btn-lg w-100 mt-3" type="submit">
                            <i class="fas fa-check-circle me-1"></i> Kirim Laporan Selesai
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- PDF Submission Form --}}
            @if($assignment->file_path)
                @php
                    $isPdf = Str::endsWith(strtolower($assignment->file_path), '.pdf');
                    $fileExtension = pathinfo($assignment->file_path, PATHINFO_EXTENSION);
                    $fileIcon = match(strtolower($fileExtension)) {
                        'pdf' => 'fa-file-pdf text-danger',
                        'doc', 'docx' => 'fa-file-word text-primary',
                        'xls', 'xlsx' => 'fa-file-excel text-success',
                        'ppt', 'pptx' => 'fa-file-powerpoint text-warning',
                        default => 'fa-file-alt text-secondary'
                    };
                @endphp
                <div class="card border-0 shadow-sm mb-4 reveal reveal-delay-1" style="border-radius: var(--radius-md) !important;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold text-dark mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas {{ $fileIcon }} me-2"></i> File Soal / Instruksi</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <a href="{{ route('assignments.download', $assignment) }}" target="_blank" class="btn btn-outline-secondary-theme btn-sm">
                                <i class="fas fa-download me-1"></i> Unduh Dokumen
                            </a>
                        </div>
                        @if($isPdf)
                            <div class="rounded border" style="overflow: hidden; height: 500px; background: #f8f9fa;">
                                <iframe src="{{ route('assignments.download', $assignment) }}" width="100%" height="100%" frameborder="0"></iframe>
                            </div>
                        @else
                            <div class="card p-4 border text-center shadow-sm" style="border-radius: var(--radius-md) !important; background: rgba(27, 94, 32, 0.015); border-color: rgba(27, 94, 32, 0.08) !important;">
                                <i class="fas {{ $fileIcon }} fa-3x mb-3"></i>
                                <h6 class="fw-bold text-dark mb-1">Dokumen Tugas ({{ strtoupper($fileExtension) }})</h6>
                                <p class="text-muted small mb-0">File ini tidak dapat ditampilkan langsung di browser. Silakan unduh untuk membukanya.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="content-card mb-4 reveal reveal-delay-2">
                <div class="content-card-body">
                    <h5 class="fw-bold text-dark mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-paper-plane text-primary me-2"></i> Kirim Jawaban</h5>
                    <form method="POST" action="{{ route('siswa.assignments.submit', $assignment) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jawaban Teks (Opsional)</label>
                            <textarea class="form-control" name="answer_text" placeholder="Ketik jawaban Anda di sini..." rows="4"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Upload File Dokumen (Opsional)</label>
                            <input type="file" class="form-control" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" onchange="validateFileSize(this)">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle text-primary me-1"></i> Format file: PDF, Word, Excel, PPT. <strong class="text-dark">Maksimal 10 MB</strong>.</small>
                        </div>
                        <button class="btn btn-primary btn-lg w-100 mt-3" type="submit">
                            <i class="fas fa-check-circle me-1"></i> Kirim Tugas
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endif

@push('scripts')
<script>
function validateFileSize(input, maxMb = 10) {
    if (input.files && input.files[0]) {
        const fileSizeMb = (input.files[0].size / (1024 * 1024)).toFixed(2);
        const maxSizeMb = maxMb;
        if (input.files[0].size > maxSizeMb * 1024 * 1024) {
            alert(`⚠️ Ukuran file terlalu besar (${fileSizeMb} MB)!\n\nBatas maksimum file yang diizinkan adalah ${maxSizeMb} MB. Silakan pilih file lain.`);
            input.value = '';
        }
    }
}
</script>
@endpush
@endsection
