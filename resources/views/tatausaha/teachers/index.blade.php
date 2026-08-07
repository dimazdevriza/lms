@extends('layouts.lms')

@section('title', 'Data Guru')

@section('content')
    {{-- Page Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 reveal">
        <div>
            <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--primary); margin-bottom: 4px;">
                Kelola Data Guru
            </h1>
            <p class="text-muted mb-0 small">Manajemen data guru aktif</p>
        </div>
        <a class="btn btn-outline-primary-theme" href="{{ route('tatausaha.teachers.create') }}">
            <i class="fas fa-plus me-1"></i> Tambah Guru
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('tatausaha.teachers.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Cari Guru</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama, email, NIP, atau telepon..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Urutan</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="name_asc" @selected(request('sort') == 'name_asc')>Nama (A - Z)</option>
                        <option value="name_desc" @selected(request('sort') == 'name_desc')>Nama (Z - A)</option>
                        <option value="latest" @selected(request('sort', 'name_asc') == 'latest')>Akun Terbaru</option>
                        <option value="earliest" @selected(request('sort', 'name_asc') == 'earliest')>Akun Terlama</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    <a href="{{ route('tatausaha.teachers.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="content-card reveal reveal-delay-1">
        <div class="content-card-header">
            <div class="content-card-header-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h2 class="content-card-title">Daftar Guru</h2>
        </div>
        <div class="content-card-body" style="padding: 0 0 8px;">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIP</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td><strong>{{ $teacher->user?->name ?? '-' }}</strong></td>
                            <td>{{ $teacher->user?->email ?? '-' }}</td>
                            <td>
                                <code style="background: rgba(27, 94, 32, 0.06); color: var(--primary); padding: 2px 8px; border-radius: 6px; font-size: 0.85rem;">
                                    {{ $teacher->nip ?? '-' }}
                                </code>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('tatausaha.teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-primary-theme">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('tatausaha.teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus guru ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="empty-state-text">
                                        @if(request()->filled('search'))
                                            Tidak ada data guru ditemukan untuk pencarian "{{ request('search') }}".
                                        @else
                                            Belum ada data guru
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $teachers->links() }}
    </div>
@endsection
