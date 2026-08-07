@extends('layouts.lms')

@section('title', 'Mata Pelajaran')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.5rem;">📚 Daftar Mata Pelajaran</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelola semua mata pelajaran di sekolah</p>
        </div>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-lg">
            ➕ Tambah Mata Pelajaran
        </a>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: var(--radius-sm);">
            ✓ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabel Mata Pelajaran -->
    <div class="content-card">
        <div class="content-card-header">
            <div class="content-card-header-icon">📋</div>
            <h5 class="content-card-title">Daftar Mata Pelajaran</h5>
        </div>
        <div class="content-card-body" style="padding-top: 12px;">
            @if($subjects->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Mata Pelajaran</th>
                                <th>Kode</th>
                                <th>Jurusan</th>
                                <th class="text-center" style="width: 160px; white-space: nowrap;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $subject)
                                <tr>
                                    <td>
                                        <strong style="color: var(--primary);">{{ $subject->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background: rgba(249, 168, 37, 0.15); color: #B26A00;">
                                            {{ $subject->code ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $majorClass = match($subject->major) {
                                                'IPA' => 'status-badge--hadir',
                                                default => ''
                                            };
                                            $majorStyle = match($subject->major) {
                                                'IPA' => '',
                                                'IPS' => 'background: rgba(249, 168, 37, 0.12); color: #B26A00;',
                                                default => 'background: rgba(0,0,0,0.05); color: var(--text-muted);'
                                            };
                                        @endphp
                                        <span class="status-badge {{ $majorClass }}" @if($majorStyle) style="{{ $majorStyle }}" @endif>
                                            {{ $subject->major ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="white-space: nowrap;">
                                        <div class="d-inline-flex gap-1 justify-content-center align-items-center flex-nowrap">
                                            <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-outline-primary-theme rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;">
                                                ✏️ Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus mata pelajaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;">🗑️ Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $subjects->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-book" style="font-size: 1.75rem; color: var(--secondary); opacity: 0.5;"></i>
                    </div>
                    <p class="empty-state-text">Belum ada mata pelajaran. <a href="{{ route('admin.subjects.create') }}">Tambah sekarang</a></p>
                </div>
            @endif
        </div>
    </div>
@endsection
