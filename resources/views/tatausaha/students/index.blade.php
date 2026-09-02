@extends('layouts.lms')

@section('title', 'Data Siswa')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="h3 mb-0">Kelola Data Siswa</h1>
        <div class="d-flex gap-2">
            <form action="{{ route('parent.code.regenerate-all') }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin memperbarui kode akses orang tua untuk SEMUA siswa? Tindakan ini biasanya dilakukan saat Tahun Ajaran Baru. Semua sesi login orang tua lama akan otomatis direset.');">
                @csrf
                <button type="submit" class="btn btn-outline-warning" title="Perbarui Semua Kode Ortu untuk Tahun Ajaran Baru">
                    <i class="fas fa-sync-alt me-1"></i> Reset Semua Kode Ortu (Tahun Ajaran Baru)
                </button>
            </form>
            <a class="btn btn-primary" href="{{ route('tatausaha.students.create') }}">
                <i class="fas fa-plus me-1"></i> Tambah Siswa
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('tatausaha.students.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Cari Siswa</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama, email, atau NISN..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Filter Jurusan</label>
                    <select name="major" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Jurusan</option>
                        <option value="IPA" @selected(request('major') == 'IPA')>🧪 IPA</option>
                        <option value="IPS" @selected(request('major') == 'IPS')>🧪 IPS</option>
                        <option value="Bahasa" @selected(request('major') == 'Bahasa')>📖 Bahasa</option>
                        <option value="Umum" @selected(request('major') == 'Umum')>📂 Umum</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Filter Kelas</label>
                    <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                                {{ $class->name }} ({{ $class->major }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Urutan</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="name_asc" @selected(request('sort') == 'name_asc')>Nama (A - Z)</option>
                        <option value="name_desc" @selected(request('sort') == 'name_desc')>Nama (Z - A)</option>
                        <option value="latest" @selected(request('sort', 'name_asc') == 'latest')>Akun Terbaru</option>
                        <option value="earliest" @selected(request('sort', 'name_asc') == 'earliest')>Akun Terlama</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    <a href="{{ route('tatausaha.students.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Jurusan</th>
                    <th>Kelas</th>
                    <th>Kode Akses Ortu</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->nisn }}</td>
                        <td>{{ $student->user?->name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $student->schoolClass?->major == 'IPA' ? 'bg-primary' : ($student->schoolClass?->major == 'IPS' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ $student->schoolClass?->major ?? '-' }}
                            </span>
                        </td>
                        <td>{{ $student->schoolClass?->name ?? '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2 allow-copy">
                                <code class="bg-light px-2 py-1 rounded text-dark fw-bold border" style="font-size: 0.85rem;">{{ $student->parent_code }}</code>
                                <button class="btn btn-sm p-0 border-0 text-success copy-btn" data-code="{{ $student->parent_code }}" title="Salin Kode Ortu">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <a href="{{ route('parent.view', $student->parent_code) }}" target="_blank" class="btn btn-sm p-0 border-0 text-primary" title="Buka Halaman Pemantauan">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <form action="{{ route('parent.code.regenerate', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Kode akses lama untuk siswa {{ $student->user?->name }} akan tidak berlaku lagi. Lanjutkan?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm p-0 border-0 text-warning" title="Perbarui Kode Ortu Siswa Ini">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('tatausaha.students.edit', $student) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('tatausaha.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">
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
                        <td colspan="6" class="text-center text-muted py-4">
                            @if(request()->filled('search'))
                                Tidak ada data siswa ditemukan untuk pencarian "{{ request('search') }}".
                            @else
                                Belum ada data.
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $students->links() }}
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const code = this.getAttribute('data-code');
                navigator.clipboard.writeText(code).then(() => {
                    alert('Kode akses orang tua berhasil disalin: ' + code);
                }).catch(err => {
                    console.error('Gagal menyalin kode: ', err);
                });
            });
        });
    });
</script>
@endpush

