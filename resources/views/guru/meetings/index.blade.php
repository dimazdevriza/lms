@extends('layouts.lms')

@section('title', 'Ruang Kelas (Materi & Tugas)')

@section('content')
    <!-- Header Banner -->
    <div class="header-banner mb-5 p-4 rounded-4 text-white d-flex align-items-center justify-content-between reveal" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); box-shadow: 0 4px 24px rgba(37, 103, 30, 0.12); border-radius: 20px;">
        <div>
            <h1 class="h2 mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800;">📅 Pertemuan Kelas</h1>
            <p class="mb-0 text-white-50">Pilih kelas dan mata pelajaran untuk melihat atau membuat pertemuan</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-light px-3 py-2 shadow-sm d-flex align-items-center gap-2" style="border-radius: 12px; font-weight: 600;" href="{{ route('guru.meetings.mandiri.create') }}" title="Pilih mapel & kelas sendiri tanpa melalui TU">
                <i class="fas fa-chalkboard-user"></i> Tambah Kelas Mandiri
            </a>
            <a class="btn btn-light px-3 py-2 shadow-sm d-flex align-items-center gap-2" style="border-radius: 12px; font-weight: 600; color: var(--primary);" href="{{ route('guru.meetings.create') }}">
                <i class="fas fa-plus"></i> Buat Pertemuan Baru
            </a>
        </div>
    </div>

    @if($meetingGroups->isEmpty())
        <div class="empty-state py-5 reveal text-center">
            <div class="empty-state-icon mb-4">
                <i class="fas fa-chalkboard-teacher fa-4x text-success" style="opacity: 0.6;"></i>
            </div>
            <h4 class="empty-state-text" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700;">📭 Belum Ada Pertemuan</h4>
            <p class="text-muted mb-4 max-w-md mx-auto">Anda belum membuat pertemuan kelas. Klik tombol di bawah untuk memulai pertemuan pertama Anda.</p>
            <a href="{{ route('guru.meetings.create') }}" class="btn btn-success px-4 py-2" style="background-color: var(--primary); border: none; border-radius: 12px; font-weight: 600;">
                Buat Pertemuan Pertama
            </a>
        </div>
    @else
        @foreach($meetingGroups as $major => $subjectGroups)
            <div class="mb-5 reveal">
                <div class="d-flex align-items-center mb-4">
                    <span class="badge {{ $major == 'IPA' ? 'bg-primary-subtle text-primary border border-primary-subtle' : ($major == 'IPS' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle') }} px-3 py-2 rounded-pill fw-bold" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.85rem;">
                        <i class="fas {{ $major == 'IPA' ? 'fa-atom' : ($major == 'IPS' ? 'fa-landmark' : 'fa-folder') }} me-2"></i> JURUSAN {{ strtoupper($major) }}
                    </span>
                    <div class="flex-grow-1 ms-3 border-top" style="border-color: rgba(37, 103, 30, 0.08) !important;"></div>
                </div>

                @foreach($subjectGroups as $subjectName => $groups)
                    <div class="ms-lg-4 mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <h5 class="mb-0 fw-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                <i class="fas fa-bookmark text-success me-2 small"></i> {{ $subjectName }}
                            </h5>
                            <span class="ms-3 badge rounded-pill bg-light text-muted border px-2 py-1" style="font-size: 0.75rem;">
                                {{ $groups->count() }} Kelas
                            </span>
                        </div>
                        
                        <div class="row">
                            @foreach($groups as $group)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="content-card class-group-card h-100 border-0 shadow-sm" 
                                         style="cursor: pointer; border-left: 4px solid {{ $major == 'IPA' ? 'var(--primary)' : ($major == 'IPS' ? 'var(--accent)' : 'var(--secondary)') }}; transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);"
                                         onclick="window.location='{{ route('guru.meetings.class-meetings', ['classSlug' => $group->schoolClass->slug, 'subjectSlug' => $group->subject->slug]) }}'">
                                        
                                        <div class="content-card-body p-4">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="p-2 rounded-circle me-3" style="background-color: {{ $major == 'IPA' ? 'rgba(27, 94, 32, 0.06)' : ($major == 'IPS' ? 'rgba(249, 168, 37, 0.06)' : 'rgba(67, 160, 71, 0.06)') }}; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-users-viewfinder {{ $major == 'IPA' ? 'text-primary' : ($major == 'IPS' ? 'text-warning' : 'text-success') }} small"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <h6 class="mb-0 fw-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif;">{{ $group->schoolClass?->name ?? 'Tanpa Kelas' }}</h6>
                                                            @if($group->is_mandiri)
                                                                <span class="badge bg-info text-white" style="font-size: 0.6rem; padding: 0.2rem 0.4rem; border-radius: 4px;">Mandiri</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted" style="font-size: 0.75rem;">{{ $group->total_meetings }} Pertemuan</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2 mt-4 pt-3 border-top" style="border-color: rgba(37, 103, 30, 0.04) !important;">
                                                <a href="{{ route('guru.meetings.class-meetings', ['classSlug' => $group->schoolClass->slug, 'subjectSlug' => $group->subject->slug]) }}" class="btn btn-sm flex-grow-1 rounded-pill py-2 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.8rem; font-weight: 600; background-color: {{ $major == 'IPA' ? 'rgba(27, 94, 32, 0.06)' : ($major == 'IPS' ? 'rgba(249, 168, 37, 0.06)' : 'rgba(67, 160, 71, 0.06)') }}; color: {{ $major == 'IPA' ? 'var(--primary-light)' : ($major == 'IPS' ? 'var(--accent)' : 'var(--secondary)') }}; border: 1px solid transparent;">
                                                    Buka Kelas <i class="fas fa-arrow-right small"></i>
                                                </a>
                                                <a href="{{ route('guru.meetings.class-meetings.create', ['classSlug' => $group->schoolClass->slug, 'subjectSlug' => $group->subject->slug]) }}" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border-color: rgba(37, 103, 30, 0.15); color: var(--primary);" title="Tambah Sesi" onclick="event.stopPropagation(); text-decoration: none;">
                                                    <i class="fas fa-plus small"></i>
                                                </a>
                                                @if($group->is_mandiri)
                                                <form action="{{ route('guru.meetings.mandiri.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas mandiri ini? Jika ada materi atau pertemuan di dalamnya, maka Anda tidak bisa mengaksesnya lagi dari sini.');" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Hapus Kelas Mandiri" onclick="event.stopPropagation();">
                                                        <i class="fas fa-trash small"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    <style>
        .class-group-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(37, 103, 30, 0.08) !important;
        }
        .class-group-card:hover .btn-sm.flex-grow-1 {
            background-color: var(--primary) !important;
            color: white !important;
        }
    </style>
@endsection
