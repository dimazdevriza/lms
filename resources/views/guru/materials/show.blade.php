@extends('layouts.lms')

@section('title', $material->title)

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div class="d-flex align-items-center gap-3">
            @php
                $backUrl = $material->meeting_id 
                    ? route('guru.meetings.show', $material->meeting_id) 
                    : route('guru.materials.index');
            @endphp
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary-theme btn-sm" style="border-radius: var(--radius-sm);">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">📚 Detail Materi</h1>
                <p class="text-muted mb-0">Informasi lengkap materi pembelajaran</p>
            </div>
        </div>
    </div>

    <div class="content-card reveal reveal-delay-1 mb-4">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <h5 class="content-card-title mb-0">Materi Pembelajaran</h5>
        </div>
        <div class="content-card-body p-4">
            <h3 class="fw-bold mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary);">{{ $material->title }}</h3>
            
            <div class="d-flex gap-2 flex-wrap mb-4 pb-4 border-bottom" style="border-color: rgba(37, 103, 30, 0.06) !important;">
                <span class="status-badge status-badge--hadir"><i class="fas fa-book me-1"></i> {{ $material->subject->name }}</span>
                <span class="status-badge status-badge--izin"><i class="fas fa-users me-1"></i> {{ $material->schoolClass->name }}</span>
                @if($material->meeting)
                    <span class="status-badge status-badge--sakit"><i class="fas fa-calendar-day me-1"></i> Pertemuan {{ $material->meeting->number }}</span>
                @else
                    <span class="status-badge status-badge--aktif"><i class="fas fa-star me-1"></i> Materi Mandiri</span>
                @endif
                <span class="status-badge" style="background: rgba(0,0,0,0.04); color: var(--text-muted);"><i class="fas fa-clock me-1"></i> {{ $material->created_at->format('d M Y, H:i') }}</span>
            </div>

            @if($material->youtube_url)
                <div class="mb-5">
                    <h5 class="fw-bold mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary);"><i class="fab fa-youtube text-danger me-2"></i> Video Pembelajaran</h5>
                    @if($material->youtube_embed_url)
                        <div class="ratio ratio-16x9 bg-dark rounded overflow-hidden shadow-sm" style="max-width: 800px; margin: 0 auto; border-radius: var(--radius-lg) !important;">
                           <iframe src="{{ $material->youtube_embed_url }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="alert alert-warning" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-exclamation-triangle me-2"></i> Link YouTube tidak valid: <a href="{{ $material->youtube_url }}" target="_blank">{{ $material->youtube_url }}</a>
                        </div>
                    @endif
                </div>
            @endif

            @if($material->content)
                <div class="mb-5">
                    <h5 class="fw-bold mb-3" style="font-family: 'Plus Jakarta Sans', sans-serif; color: var(--primary);"><i class="fas fa-align-left me-2"></i> Catatan Materi</h5>
                    <div class="p-4 rounded text-dark" style="font-size: 1.05rem; line-height: 1.6; background-color: var(--bg-body); border: 1px solid rgba(27, 94, 32, 0.04); border-radius: var(--radius-md) !important;">
                        {!! nl2br(e($material->content)) !!}
                    </div>
                </div>
            @endif

            @if($material->file_path)
                <div class="mt-4 pt-4 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0" style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fas fa-file-pdf text-danger me-2"></i> Lampiran Dokumen</h5>
                        @if($material->hasPhysicalFile())
                            <a href="{{ route('materials.view-file', $material) }}" target="_blank" class="small text-decoration-none d-none d-md-inline" style="color: var(--secondary); font-weight: 600;">
                                <i class="fas fa-external-link-alt me-1"></i> Buka di Tab Baru
                            </a>
                        @endif
                    </div>
                    
                    @if(!$material->hasPhysicalFile())
                        <div class="alert alert-warning border-0 shadow-sm p-4 d-flex align-items-start gap-3" style="border-radius: var(--radius-md); background: #fff8e1; border-left: 5px solid #ffc107 !important;">
                            <div class="text-warning fs-3 flex-shrink-0"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">Berkas PDF Belum Tersimpan di Server Baru</h6>
                                <p class="small text-muted mb-3">
                                    Data materi ini tercatat memiliki file lampiran, namun file fisik PDF belum tersedia di penyimpanan server baru. Siswa tidak dapat mengunduh materi ini sebelum Anda mengunggah ulang berkasnya.
                                </p>
                                <a href="{{ route('guru.materials.edit', $material) }}" class="btn btn-warning btn-sm fw-bold px-3 py-2 text-dark" style="border-radius: var(--radius-sm);">
                                    <i class="fas fa-upload me-1"></i> Upload Berkas Sekarang
                                </a>
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
            
            <div class="mt-5 pt-4 border-top d-flex justify-content-end" style="border-color: rgba(37, 103, 30, 0.06) !important;">
                <a href="{{ route('guru.materials.edit', $material) }}" class="btn btn-outline-accent-theme fw-bold px-4 py-2" style="background-color: var(--accent) !important; color: #4E3400 !important; border: none; border-radius: var(--radius-md);">
                    <i class="fas fa-edit me-1"></i> Edit Materi
                </a>
            </div>
        </div>
    </div>
@endsection
