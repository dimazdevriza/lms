@extends('layouts.lms')

@section('title', 'Forum Kelas - ' . $subject->name)

@section('content')
    @php
        // ponytail: route guru back to the specific class meetings index instead of general dashboard
        $backUrl = Auth::user()->role === 'siswa' 
            ? route('siswa.subjects.show', $subject->id) 
            : route('guru.meetings.class-meetings', ['classSlug' => $schoolClass->slug, 'subjectSlug' => $subject->slug]);
    @endphp

    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 mb-5 reveal">
        <a href="{{ $backUrl }}" class="btn btn-outline-secondary-theme btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <nav aria-label="breadcrumb" class="mb-1">
                <ol class="breadcrumb mb-1">
                    @if(Auth::user()->role === 'siswa')
                        <li class="breadcrumb-item"><a href="{{ route('siswa.subjects.index') }}" style="color: var(--secondary);">Mata Pelajaran</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('siswa.subjects.show', $subject->id) }}" style="color: var(--secondary);">{{ $subject->name }}</a></li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('guru.dashboard') }}" style="color: var(--secondary);">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('guru.meetings.class-meetings', ['classSlug' => $schoolClass->slug, 'subjectSlug' => $subject->slug]) }}" style="color: var(--secondary);">Pertemuan</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">Forum</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary) !important;">
                <i class="fas fa-comments me-2 text-warning"></i> Forum Diskusi: {{ $subject->name }} ({{ $schoolClass->name }})
            </h1>
        </div>
    </div>

    <div class="row">
        <!-- Main Forum Flow -->
        <div class="col-lg-8 mb-4">
            <!-- Create Post Card -->
            <div class="card border-0 shadow-sm mb-4 reveal" style="border-radius: var(--radius-md) !important;">
                <div class="card-body p-4">
                    <form action="{{ route('forum.post.store', ['classSlug' => $schoolClass->slug, 'subjectSlug' => $subject->slug]) }}" method="POST">
                        @csrf
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <textarea name="content" class="form-control border-0 bg-light p-3" rows="3" 
                                          style="border-radius: var(--radius-sm); resize: none; focus: none;" 
                                          placeholder="Bagikan pengumuman atau diskusikan sesuatu dengan kelas..." required></textarea>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-3" style="font-size: 0.75rem;">
                            <i class="fas fa-shield-alt me-1 text-warning"></i>
                            Harap gunakan bahasa yang sopan & hormati sesama pengguna. Hindari konten yang menyinggung, kasar, atau tidak pantas.
                        </small>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="submit" class="btn btn-primary-theme px-4">
                                <i class="fas fa-paper-plane me-1"></i> Bagikan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Posts List -->
            @forelse($posts as $post)
                <div class="card border-0 shadow-sm mb-4 reveal" style="border-radius: var(--radius-md) !important;">
                    <div class="card-body p-4">
                        <!-- Post Author & Meta -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" 
                                     style="width: 42px; height: 42px; font-size: 1.1rem;">
                                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $post->user->name }}</h6>
                                    <div class="d-flex align-items-center gap-2 small text-muted">
                                        <span class="badge {{ $post->user->role === 'guru' ? 'bg-danger' : 'bg-secondary' }}" style="font-size: 0.65rem;">
                                            {{ strtoupper($post->user->role) }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Delete Option -->
                            @if($post->user_id === Auth::id() || Auth::user()->role === 'guru' || Auth::user()->role === 'admin')
                                <form action="{{ route('forum.post.destroy', $post->id) }}" method="POST" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0" title="Hapus Postingan">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Post Content -->
                        <div class="text-dark mb-4" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">{{ $post->content }}</div>

                        <!-- Comments Section -->
                        <div class="border-top pt-3">
                            <h6 class="text-muted small mb-3">
                                <i class="far fa-comment-alt me-1"></i> {{ $post->comments->count() }} Komentar
                            </h6>

                            <!-- Existing Comments -->
                            @if($post->comments->isNotEmpty())
                                <div class="d-flex flex-column mb-3">
                                    @foreach($post->comments as $comment)
                                        @include('partials.discussion-comment-node', ['comment' => $comment, 'post' => $post, 'depth' => 1])
                                    @endforeach
                                </div>
                            @endif

                            <!-- Comment Input Form -->
                            <form action="{{ route('forum.comment.store', $post->id) }}" method="POST" class="mt-2">
                                @csrf
                                <div class="input-group">
                                    <input type="text" name="content" class="form-control bg-light border-0 py-2 px-3" 
                                           placeholder="Tulis komentar..." required style="border-radius: var(--radius-sm) 0 0 var(--radius-sm);">
                                    <button class="btn btn-outline-primary-theme" type="submit" style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0;">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                    <i class="fas fa-info-circle me-1"></i> Gunakan bahasa yang sopan. Komentar yang bersifat kasar atau menyinggung dapat dihapus.
                                </small>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm reveal">
                    <div class="card-body py-5 text-center">
                        <div class="text-muted mb-3" style="font-size: 3rem;">
                            <i class="far fa-comments"></i>
                        </div>
                        <h5 class="fw-bold">Belum ada diskusi di forum ini</h5>
                        <p class="text-muted small">Mulai obrolan baru dengan membagikan pengumuman atau pertanyaan.</p>
                    </div>
                </div>
            @endforelse

            <div class="mt-3">
                {{ $posts->links() }}
            </div>
        </div>

        <!-- Sidebar Section -->
        <div class="col-lg-4">
            <!-- Subject Info Card -->
            <div class="card border-0 shadow-sm mb-4 reveal reveal-delay-1" style="border-radius: var(--radius-md) !important;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary) !important;">
                        <i class="fas fa-folder-open me-2 text-primary"></i> Detail Kelas
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <small class="text-muted d-block">Mata Pelajaran</small>
                            <span class="fw-bold text-dark">{{ $subject->name }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Kelas</small>
                            <span class="fw-bold text-dark">{{ $schoolClass->name }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Jurusan</small>
                            <span class="fw-bold text-dark">{{ $schoolClass->major ?? 'Umum' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Toggle reply form script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-reply').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var target = document.getElementById(this.getAttribute('data-target'));
                if (target) {
                    target.classList.toggle('d-none');
                    if (!target.classList.contains('d-none')) {
                        target.querySelector('input[name="content"]').focus();
                    }
                }
            });
        });
    });
    </script>
@endsection
