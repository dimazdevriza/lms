@extends('layouts.lms')

@section('title', $material->title)

@section('content')
    <!-- ponytail: responsive header block -->
    <div class="mb-4 reveal">
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3 mb-3">
            @if($material->meeting)
                <a href="{{ route('siswa.meetings.show', $material->meeting) }}" class="btn btn-outline-secondary-theme btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            @else
                <a href="{{ route('siswa.subjects.show', $material->subject_id) }}" class="btn btn-outline-secondary-theme btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            @endif
            <h1 class="h3 mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary);">Materi Pembelajaran</h1>
        </div>
    </div>

    <div class="content-card mb-5 reveal reveal-delay-1">
        <div class="content-card-header" style="padding-bottom: 0;">
            <div class="content-card-header-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h5 class="content-card-title" style="font-size: 1.35rem; font-weight: 800; line-height: 1.4;">{{ $material->title }}</h5>
        </div>
        <div class="content-card-body">
            <!-- ponytail: premium, responsive metadata row instead of bulky badges -->
            <div class="d-flex flex-wrap align-items-center gap-2 pb-3 mb-4 border-bottom" style="font-size: 0.9rem; color: var(--text-body);">
                <span class="d-flex align-items-center gap-2"><i class="fas fa-book text-success"></i> <strong>{{ $material->subject->name }}</strong></span>
                <span class="mx-2 text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                <span class="d-flex align-items-center gap-2"><i class="fas fa-chalkboard-user text-primary"></i> {{ $material->teacher->user->name }}</span>
                @if($material->meeting)
                    <span class="mx-2 text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                    <span class="d-flex align-items-center gap-2"><i class="fas fa-calendar-day text-warning"></i> Pertemuan {{ $material->meeting->number }}</span>
                @endif
                <span class="mx-2 text-muted opacity-50 d-none d-sm-inline">&bull;</span>
                <span class="d-flex align-items-center gap-2"><i class="fas fa-clock text-info"></i> {{ $material->created_at->format('d M Y, H:i') }}</span>
            </div>

            <!-- ponytail: modern wrapped video frame -->
            @if($material->youtube_url)
                <div class="mb-5 reveal reveal-delay-2">
                    @if($material->youtube_embed_url)
                        <div class="p-2 rounded-4 border shadow-sm bg-light" style="max-width: 800px; margin: 0 auto; border-color: rgba(27, 94, 32, 0.08) !important;">
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                                <iframe src="{{ $material->youtube_embed_url }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-video me-2"></i> Terdapat lampiran video, namun format link tidak didukung. <a href="{{ $material->youtube_url }}" target="_blank" class="alert-link">Buka di YouTube</a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- ponytail: editorial-style body content container -->
            @if($material->content)
                <div class="mb-5 reveal reveal-delay-3">
                    <h5 class="fw-bold mb-3" style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-align-left me-2"></i> Deskripsi Materi</h5>
                    <div class="p-4 rounded-3" style="background: rgba(27, 94, 32, 0.015); font-size: 1.05rem; line-height: 1.8; border: 1px solid rgba(27, 94, 32, 0.05); color: #333;">
                        {!! nl2br(e($material->content)) !!}
                    </div>
                </div>
            @endif

            <!-- ponytail: embedded PDF viewer frame for easy reading -->
            @if($material->file_path)
                <div class="mt-5 pt-4 border-top reveal reveal-delay-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0" style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-file-pdf text-danger me-2"></i> Lampiran Dokumen</h5>
                        @if($material->hasPhysicalFile())
                            <a href="{{ route('materials.view-file', $material) }}" target="_blank" class="small text-decoration-none d-none d-md-inline" style="color: var(--secondary); font-weight: 600;">
                                <i class="fas fa-external-link-alt me-1"></i> Buka di Tab Baru
                            </a>
                        @endif
                    </div>
                    
                    @if(!$material->hasPhysicalFile())
                        <div class="alert alert-info border-0 shadow-sm p-4 d-flex align-items-start gap-3" style="border-radius: var(--radius-md); background: #e8f5e9; border-left: 4px solid var(--primary) !important;">
                            <div class="text-success fs-3 flex-shrink-0"><i class="fas fa-info-circle"></i></div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Berkas Lampiran Sedang Diperbarui</h6>
                                <p class="small text-muted mb-0">
                                    Berkas dokumen untuk materi ini sedang dalam proses pembaruan oleh Guru pengampu. Silakan pelajari ringkasan materi atau catatan di atas terlebih dahulu.
                                </p>
                            </div>
                        </div>
                    @else
                        <!-- Mobile Fallback Card (Mobile browsers block inline PDF iframes) -->
                        <div class="d-block d-md-none mb-3">
                            <div class="card p-4 border text-center shadow-sm" style="border-radius: var(--radius-md) !important; background: rgba(27, 94, 32, 0.015); border-color: rgba(27, 94, 32, 0.08) !important;">
                                <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                <h6 class="fw-bold text-dark mb-1">Materi PDF</h6>
                                <p class="text-muted small mb-4">Browser mobile tidak dapat menampilkan PDF secara langsung di halaman.</p>
                                <a href="{{ route('materials.view-file', $material) }}" target="_blank" class="btn btn-sm btn-outline-primary-theme w-100 py-2.5 fw-bold" style="background: var(--primary) !important; color: white !important; border: none; border-radius: var(--radius-sm);">
                                    <i class="fas fa-external-link-alt me-1"></i> Buka Dokumen PDF
                                </a>
                            </div>
                        </div>

                        <!-- Desktop PDF Iframe -->
                        <div class="d-none d-md-block border rounded-3 shadow-sm overflow-hidden" style="height: 600px; background-color: #f8f9fa; border-color: rgba(27, 94, 32, 0.08) !important;">
                            <iframe src="{{ route('materials.view-file', $material) }}" width="100%" height="100%" style="border: none;"></iframe>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
