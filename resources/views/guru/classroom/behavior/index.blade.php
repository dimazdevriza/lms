@extends('layouts.lms')

@section('title', 'Catatan Perilaku - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">📝 Catatan Perilaku Siswa</h1>
                    <p>Kelas {{ $class->name }} • Kelola catatan perilaku positif dan negatif siswa</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('guru.classroom.index') }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('guru.classroom.behavior.create', $class) }}" class="btn btn-light d-inline-flex align-items-center gap-2" style="color: var(--primary) !important; font-weight: 700;">
                        <i class="fas fa-plus"></i> Tambah Catatan Perilaku
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm reveal" role="alert" style="border-radius: var(--radius-md);">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Daftar Catatan Perilaku -->
    <div class="content-card reveal reveal-delay-1">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <i class="fas fa-history"></i>
            </div>
            <h5 class="content-card-title">Riwayat Catatan Perilaku</h5>
        </div>
        <div class="content-card-body">
            @if($behaviors->count() > 0)
                <div class="d-flex flex-column gap-3">
                    @foreach($behaviors as $behavior)
                        <div class="behavior-card {{ $behavior->type === 'positif' ? 'behavior-card--prestasi' : 'behavior-card--pelanggaran' }} border border-light shadow-sm">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <h6 class="behavior-card-title {{ $behavior->type === 'positif' ? 'behavior-card-title--prestasi' : 'behavior-card-title--pelanggaran' }} mb-0">
                                            {{ $behavior->student?->user?->name ?? 'Siswa (Tanpa Nama)' }}
                                        </h6>
                                        <span class="behavior-type-badge {{ $behavior->type === 'positif' ? 'behavior-type-badge--prestasi' : 'behavior-type-badge--pelanggaran' }}">
                                            {{ $behavior->type === 'positif' ? 'Positif' : 'Negatif' }}
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">{{ $behavior->title }}</h5>
                                    <p class="behavior-card-desc mb-2 text-muted">{{ $behavior->description }}</p>
                                    <div class="behavior-card-date">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $behavior->date->translatedFormat('d F Y') }}
                                    </div>
                                </div>
                                <div>
                                    <form method="POST" action="{{ route('guru.classroom.behavior.destroy', [$class, $behavior]) }}" onsubmit="return confirm('Yakin ingin menghapus catatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $behaviors->links() }}
                </div>
            @else
                <div class="empty-state py-5">
                    <div class="empty-state-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h5 class="empty-state-text mt-3">Belum Ada Catatan Perilaku</h5>
                    <p class="text-muted">Catat prestasi atau pelanggaran tata tertib siswa untuk pembinaan berkala.</p>
                    <a href="{{ route('guru.classroom.behavior.create', $class) }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Tambah Catatan Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .behavior-card--pelanggaran {
            background: linear-gradient(135deg, rgba(198, 40, 40, 0.05) 0%, rgba(198, 40, 40, 0.01) 100%);
        }
        .behavior-card--pelanggaran::before {
            background: linear-gradient(180deg, #C62828, #FF8A80);
        }
        .behavior-card--pelanggaran:hover {
            background: linear-gradient(135deg, rgba(198, 40, 40, 0.08) 0%, rgba(198, 40, 40, 0.02) 100%);
            box-shadow: 0 4px 16px rgba(198, 40, 40, 0.08);
        }
        .behavior-card-title--pelanggaran {
            color: #C62828;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .behavior-type-badge--pelanggaran {
            background: rgba(198, 40, 40, 0.1);
            color: #C62828;
        }
    </style>
    @endpush
@endsection
