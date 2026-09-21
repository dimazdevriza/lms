@extends('layouts.lms')

@section('title', 'Materi')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div>
            <h1 class="mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">📚 Kelola Materi Pembelajaran</h1>
            <p class="text-muted mb-0">Upload dan kelola materi untuk siswa Anda</p>
        </div>
        <a class="btn btn-primary fw-bold px-4 py-2" style="border-radius: var(--radius-md);" href="{{ route('guru.materials.create') }}">
            <i class="fas fa-plus me-2"></i> Upload Materi Baru
        </a>
    </div>

    <!-- Missing Materials Alert -->
    @if(($missingCount ?? 0) > 0)
        <div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 reveal" 
             style="border-radius: var(--radius-md); background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%); border-left: 5px solid #ffc107 !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" 
                     style="background: rgba(255, 193, 7, 0.25); width: 44px; height: 44px;">
                    <i class="fas fa-exclamation-triangle text-warning fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Perhatian: Ada {{ $missingCount }} Berkas Materi yang Perlu Diunggah Ulang</h6>
                    <p class="small text-muted mb-0">
                        Berkas PDF materi bertanda <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Berkas Hilang di Server</span> belum tersimpan di server. Klik tombol <strong>Upload Ulang</strong> pada kartu materi untuk melengkapi berkasnya.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Kelas -->
    @if(isset($teacherClasses) && $teacherClasses->count() > 0)
        <div class="d-flex gap-2 mb-4 flex-wrap align-items-center reveal reveal-delay-1">
            <span class="text-muted small fw-bold me-2"><i class="fas fa-filter me-1"></i>Filter Kelas:</span>
            <a href="{{ route('guru.materials.index') }}" 
               class="btn btn-sm px-3 rounded-pill fw-semibold {{ !$selectedClassId ? 'btn-primary' : 'btn-outline-secondary-theme' }}">
                Semua Kelas
            </a>
            @foreach($teacherClasses as $cls)
                <a href="{{ route('guru.materials.index', ['class_id' => $cls->id]) }}" 
                   class="btn btn-sm px-3 rounded-pill fw-semibold {{ $selectedClassId == $cls->id ? 'btn-primary' : 'btn-outline-secondary-theme' }}">
                    {{ $cls->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($materials->isEmpty())
        <div class="content-card reveal py-5">
            <div class="content-card-body text-center">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-book-open text-success"></i>
                    </div>
                    <div class="empty-state-text">
                        <strong>Belum Ada Materi Pembelajaran</strong><br>
                        Mulai dengan upload materi baru untuk siswa Anda agar mereka dapat belajar secara mandiri.
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('guru.materials.create') }}" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: var(--radius-md);"><i class="fas fa-plus me-2"></i> Upload Sekarang</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            @forelse($materials as $m)
                @php
                    $isMissingFile = $m->file_path && !$m->hasPhysicalFile();
                @endphp
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="content-card h-100 d-flex flex-column justify-content-between shadow-sm border-0 material-card overflow-hidden" 
                         style="cursor: pointer; border-left: 4px solid {{ $isMissingFile ? '#dc3545' : 'var(--primary)' }} !important; border-radius: var(--radius-md); transition: transform 0.2s ease, box-shadow 0.2s ease; {{ $isMissingFile ? 'background: #fffdfd;' : '' }}"
                         onclick="window.location='{{ route('guru.materials.show', $m) }}'">
                        
                        <div class="content-card-body p-3 flex-grow-1">
                            <!-- Top Title & Badge -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="fw-bold mb-1 text-truncate" style="color: {{ $isMissingFile ? '#dc3545' : 'var(--primary)' }}; font-family: 'Plus Jakarta Sans', sans-serif;" title="{{ $m->title }}">
                                        {{ $m->title }}
                                    </h6>
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span class="status-badge status-badge--hadir py-0 px-2" style="font-size: 0.7rem;">📖 Materi</span>
                                        @if($isMissingFile)
                                            <span class="badge bg-danger text-white py-0 px-1.5" style="font-size: 0.68rem;">
                                                <i class="fas fa-exclamation-circle me-1"></i>Perlu Upload
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge bg-light text-muted fw-normal flex-shrink-0" style="font-size: 0.7rem;">
                                    <i class="fas fa-calendar me-1"></i>{{ $m->created_at->format('d M Y') }}
                                </span>
                            </div>

                            <!-- Class & Subject -->
                            <div class="small text-muted mb-2.5 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.8rem;">
                                <span><i class="fas fa-door-open me-1 text-primary"></i>{{ $m->schoolClass?->name ?? 'Tanpa Kelas' }}</span>
                                <span>•</span>
                                <span><i class="fas fa-book me-1 text-success"></i>{{ $m->subject?->name ?? 'Tanpa Mapel' }}</span>
                            </div>

                            <!-- Meeting Info -->
                            @if($m->meeting)
                                <div class="bg-light rounded p-2 mb-3 small" style="font-size: 0.78rem;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted"><i class="fas fa-calendar-alt text-warning me-1"></i> Pertemuan:</span>
                                        <strong style="color: var(--primary);">Pertemuan {{ $m->meeting->number }}</strong>
                                    </div>
                                </div>
                            @endif

                            <!-- Attachment Indicator -->
                            @if($m->file_path)
                                @if($isMissingFile)
                                    <div class="d-flex align-items-center justify-content-between small mt-2 p-2 rounded bg-danger-subtle border border-danger-subtle" style="font-size: 0.78rem;">
                                        <span class="text-danger fw-semibold">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Berkas Hilang di Server
                                        </span>
                                        <a href="{{ route('guru.materials.edit', $m) }}" class="btn btn-sm btn-danger py-0 px-2 fw-semibold" style="font-size: 0.72rem; border-radius: var(--radius-sm);" onclick="event.stopPropagation();">
                                            <i class="fas fa-upload me-1"></i>Upload Ulang
                                        </a>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center justify-content-between small" style="font-size: 0.8rem;">
                                        <span class="status-badge py-0 px-2" style="background: rgba(220,53,69,0.08); color: #dc3545; font-size: 0.75rem;" onclick="event.stopPropagation(); window.open('{{ route('materials.view-file', $m) }}', '_blank')">
                                            <i class="fas fa-file-pdf me-1"></i>PDF Terlampir
                                        </span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Card Actions Footer -->
                        <div class="px-3 py-2 bg-white d-flex justify-content-end gap-2 align-items-center" onclick="event.stopPropagation();" style="border-top: 1px solid rgba(0,0,0,0.05);">
                            <a href="{{ route('guru.materials.show', $m) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="border-radius: var(--radius-sm); font-size: 0.8rem;" title="Lihat Detail">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                            @if($isMissingFile)
                                <a href="{{ route('guru.materials.edit', $m) }}" class="btn btn-sm btn-danger py-1 px-2.5 fw-semibold shadow-sm" style="border-radius: var(--radius-sm); font-size: 0.8rem;" title="Upload Berkas">
                                    <i class="fas fa-upload me-1"></i> Upload Berkas
                                </a>
                            @else
                                <a href="{{ route('guru.materials.edit', $m) }}" class="btn btn-sm btn-outline-primary py-1 px-2" style="border-radius: var(--radius-sm); font-size: 0.8rem;" title="Edit">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            @endif
                            <form action="{{ route('guru.materials.destroy', $m) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="border-radius: var(--radius-sm); font-size: 0.8rem;" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>

        <div class="mt-4">{{ $materials->links() }}</div>
    @endif
@endsection

@push('styles')
<style>
    .material-card:hover {
        transform: translateY(-4px) !important;
        box-shadow: 0 8px 32px rgba(37, 103, 30, 0.08) !important;
    }
</style>
@endpush
