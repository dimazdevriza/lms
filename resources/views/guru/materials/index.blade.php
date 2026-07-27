@extends('layouts.lms')

@section('title', 'Materi')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3 reveal">
        <div>
            <h1 class="mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">📚 Kelola Materi Pembelajaran</h1>
            <p class="text-muted mb-0">Upload dan kelola materi untuk siswa Anda</p>
        </div>
        <a class="btn btn-primary fw-bold px-4 py-2" style="border-radius: var(--radius-md);" href="{{ route('guru.materials.create') }}">
            <i class="fas fa-plus me-2"></i> Upload Materi Baru
        </a>
    </div>

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
                <div class="col-md-6 mb-4 reveal reveal-delay-{{ $loop->iteration }}">
                    <div class="content-card h-100 material-card overflow-hidden d-flex flex-column justify-content-between" 
                         style="cursor: pointer; border-left: 4px solid var(--primary) !important; border-radius: var(--radius-lg); transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);"
                         onclick="window.location='{{ route('guru.materials.show', $m) }}'">
                        
                        <div class="content-card-body p-4 flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title fw-bold mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;">{{ $m->title }}</h5>
                                    <span class="status-badge status-badge--hadir">📖 Materi</span>
                                </div>
                                <small class="text-muted"><i class="fas fa-calendar me-1"></i> {{ $m->created_at->format('d M Y') }}</small>
                            </div>

                            <div class="mb-3 d-flex flex-wrap gap-3">
                                <span class="d-inline-flex align-items-center small text-muted"><i class="fas fa-door-open me-2 text-primary"></i> {{ $m->schoolClass?->name ?? 'Tanpa Kelas' }}</span>
                                <span class="d-inline-flex align-items-center small text-muted"><i class="fas fa-book me-2 text-success"></i> {{ $m->subject?->name ?? 'Tanpa Mapel' }}</span>
                            </div>

                            @if($m->meeting)
                                <div class="alert alert-light py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2" style="border-radius: var(--radius-sm); background-color: var(--bg-body);">
                                    <i class="fas fa-calendar-alt text-warning"></i>
                                    <span>Terhubung ke: <strong style="color: var(--primary);">Pertemuan {{ $m->meeting->number }}</strong></span>
                                </div>
                            @endif

                            @if($m->file_path)
                                <div class="mt-3 p-3 border rounded text-center" style="border-color: rgba(37, 103, 30, 0.08) !important; background: var(--bg-body); border-radius: var(--radius-md) !important;">
                                    <i class="fas fa-file-pdf text-danger fa-2x mb-2"></i>
                                    <div class="small fw-bold text-dark">PDF Terlampir</div>
                                    <a href="{{ asset('storage/' . $m->file_path) }}" target="_blank" class="small text-decoration-none text-success fw-bold d-inline-block mt-1" onclick="event.stopPropagation();">
                                        <i class="fas fa-eye me-1"></i> Lihat Dokumen
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-white border-top-0 p-4 pt-0 d-flex justify-content-end gap-2 align-items-center" onclick="event.stopPropagation();" style="border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
                            <a href="{{ route('guru.materials.show', $m) }}" class="btn btn-sm btn-outline-secondary-theme" title="Lihat Detail">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                            <a href="{{ route('guru.materials.edit', $m) }}" class="btn btn-sm btn-outline-primary-theme" title="Edit">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('guru.materials.destroy', $m) }}" method="POST" onsubmit="return confirm('Hapus materi ini?')" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus" style="border-radius: var(--radius-sm);">
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
