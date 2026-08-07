@extends('layouts.lms')

@section('title', 'Kelola Kelas')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.5rem;">🎓 Kelola Kelas</h1>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Manage semua kelas yang tersedia</p>
        </div>
        <a class="btn btn-primary btn-lg" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
    </div>

    @if($classes->isEmpty())
        <div class="content-card">
            <div class="content-card-body">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-school" style="font-size: 1.75rem; color: var(--secondary); opacity: 0.5;"></i>
                    </div>
                    <p class="empty-state-text">Belum ada kelas. Mulai dengan membuat kelas baru untuk tahun ajaran ini.</p>
                    <a href="{{ route('admin.classes.create') }}" class="btn btn-outline-primary-theme btn-sm mt-3">+ Tambah Sekarang</a>
                </div>
            </div>
        </div>
    @else
        <div class="content-card">
            <div class="content-card-header">
                <div class="content-card-header-icon">📋</div>
                <h5 class="content-card-title">Daftar Kelas</h5>
            </div>
            <div class="content-card-body" style="padding-top: 12px;">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Tahun Ajaran</th>
                            <th>Wali Kelas</th>
                            <th class="text-center" style="width: 220px; white-space: nowrap;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($classes as $class)
                            <tr>
                                <td>
                                    <strong style="color: var(--primary);">{{ $class->name }}</strong>
                                </td>
                                <td>
                                    <span class="status-badge status-badge--hadir">{{ $class->level ?? '-' }}</span>
                                </td>
                                <td>
                                    @php
                                        $majorClass = match($class->major) {
                                            'IPA' => 'status-badge--hadir',
                                            'IPS' => '',
                                            default => ''
                                        };
                                        $majorStyle = match($class->major) {
                                            'IPA' => '',
                                            'IPS' => 'background: rgba(249, 168, 37, 0.12); color: #B26A00;',
                                            default => 'background: rgba(0,0,0,0.05); color: var(--text-muted);'
                                        };
                                    @endphp
                                    <span class="status-badge {{ $majorClass }}" @if($majorStyle) style="{{ $majorStyle }}" @endif>{{ $class->major ?? '-' }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $class->academicYear?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $class->homeroomTeacher?->user?->name ?? '-' }}</small>
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <div class="d-inline-flex gap-1 justify-content-center align-items-center flex-nowrap">
                                        <a class="btn btn-outline-info rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;" href="{{ route('admin.classes.show', $class) }}">🔍 Detail</a>
                                        <a class="btn btn-outline-primary-theme rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;" href="{{ route('admin.classes.edit', $class) }}">✏️ Edit</a>
                                        <button class="btn btn-outline-danger rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;" onclick="if(confirm('Hapus kelas ini?')) { document.getElementById('form-{{ $class->id }}').submit(); }" type="button">🗑️ Hapus</button>
                                    </div>
                                    <form id="form-{{ $class->id }}" method="POST" action="{{ route('admin.classes.destroy', $class) }}" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $classes->links() }}
        </div>
    @endif
@endsection
