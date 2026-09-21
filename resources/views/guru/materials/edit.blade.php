@extends('layouts.lms')

@section('title', 'Edit Materi')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div class="d-flex align-items-center gap-3">
            @php
                if (auth()->user()->role === 'admin') {
                    $backUrl = $material->meeting_id
                        ? route('admin.attendances.meetingMaterials', $material->meeting_id)
                        : route('admin.attendances.showSubject', ['class' => $material->class_id, 'subject' => $material->subject_id]);
                } else {
                    $backUrl = $material->meeting_id 
                        ? route('guru.meetings.show', $material->meeting_id) 
                        : route('guru.materials.index');
                }
            @endphp
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary-theme btn-sm" style="border-radius: var(--radius-sm);">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">📚 Edit Materi Pembelajaran</h1>
                <p class="text-muted mb-0">Perbarui konten materi pembelajaran Anda</p>
            </div>
        </div>
    </div>

    @if($material->file_path && !$material->hasPhysicalFile())
        <div class="alert alert-warning border-0 shadow-sm mb-4 d-flex align-items-center gap-3 p-3 reveal" 
             style="border-radius: var(--radius-md); background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%); border-left: 5px solid #ffc107 !important;">
            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" 
                 style="background: rgba(255, 193, 7, 0.25); width: 44px; height: 44px;">
                <i class="fas fa-exclamation-triangle text-warning fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Berkas Fisik Materi Belum Tersimpan di Server Baru</h6>
                <p class="small text-muted mb-0">
                    File PDF untuk materi ini belum ada di server baru setelah migrasi. Silakan pilih berkas PDF di bagian <strong>File PDF Materi</strong> di bawah, kemudian klik <strong>Simpan Perubahan</strong> agar siswa dapat mengaksesnya kembali.
                </p>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mb-4 reveal reveal-delay-1">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Formulir Edit Materi</h5>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="{{ route('guru.materials.update', $material) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Pertemuan, Kelas & Mata Pelajaran -->
                        <div class="row mb-4">
                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold" style="color: var(--primary);">🗓️ Pilih Pertemuan (Opsional)</label>
                                <select class="form-select" style="border: 2px solid var(--accent); border-radius: var(--radius-sm);" name="meeting_id" id="meeting_id">
                                    <option value="">-- Tanpa Pertemuan (Materi Mandiri) --</option>
                                    @foreach($meetings as $meeting)
                                        <option value="{{ $meeting->id }}" 
                                            data-class="{{ $meeting->class_id }}" 
                                            data-subject="{{ $meeting->subject_id }}"
                                            @selected(old('meeting_id', $material->meeting_id) == $meeting->id)>
                                            Pertemuan {{ $meeting->number }}: {{ $meeting->title }} ({{ $meeting->schoolClass->name }} - {{ $meeting->subject->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">🎓 Kelas</label>
                                <select class="form-select" name="class_id" id="class_id" required style="border-radius: var(--radius-sm);">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" @selected(old('class_id', $material->class_id) == $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">📖 Mata Pelajaran</label>
                                <select class="form-select" name="subject_id" id="subject_id" required style="border-radius: var(--radius-sm);">
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id', $material->subject_id) == $subject->id || $subjects->count() == 1)>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const meetingSelect = document.getElementById('meeting_id');
                                const classSelect = document.getElementById('class_id');
                                const subjectSelect = document.getElementById('subject_id');
                                
                                const classCol = classSelect.closest('.col-md-6');
                                const subjectCol = subjectSelect.closest('.col-md-6');

                                function updateFields() {
                                    const selectedOption = meetingSelect.options[meetingSelect.selectedIndex];
                                    if (selectedOption.value) {
                                        // Auto-fill hidden values
                                        const classId = selectedOption.getAttribute('data-class');
                                        const subjectId = selectedOption.getAttribute('data-subject');
                                        if (classId) classSelect.value = classId;
                                        if (subjectId) subjectSelect.value = subjectId;
                                        
                                        // Hide the dropdowns to keep UI clean
                                        classCol.style.display = 'none';
                                        subjectCol.style.display = 'none';
                                    } else {
                                        // Show dropdowns for Materi Mandiri
                                        classCol.style.display = 'block';
                                        subjectCol.style.display = 'block';
                                    }
                                }

                                meetingSelect.addEventListener('change', updateFields);
                                
                                if (meetingSelect.value) {
                                    updateFields();
                                }
                            });
                        </script>

                        <!-- Judul Materi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📝 Judul Materi</label>
                            <input class="form-control" name="title" value="{{ old('title', $material->title) }}" required style="border-radius: var(--radius-sm);">
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Konten -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📄 Konten Materi (Opsional)</label>
                            <textarea class="form-control" name="content" rows="6" style="border-radius: var(--radius-sm);">{{ old('content', $material->content) }}</textarea>
                            @error('content')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- File PDF -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📤 File PDF Materi</label>
                            @if($material->file_path)
                                @if(!$material->hasPhysicalFile())
                                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-3 mb-3 p-3" style="border-radius: var(--radius-sm); background: #fff8e1; border-left: 4px solid #ffc107 !important;">
                                        <div class="text-warning fs-4"><i class="fas fa-exclamation-triangle"></i></div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">Berkas Fisik Belum Tersimpan di Server Baru</h6>
                                            <p class="small text-muted mb-0">
                                                Materi ini tercatat memiliki lampiran file di database, namun file fisik PDF belum tersedia di server saat ini. Silakan pilih dan unggah berkas PDF materi Anda di bawah ini, lalu klik tombol <strong>Simpan Perubahan</strong>.
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small text-muted"><i class="fas fa-file-pdf text-danger me-1"></i> Preview PDF saat ini:</span>
                                            <a href="{{ route('materials.view-file', $material) }}" target="_blank" class="small text-decoration-none d-none d-md-inline" style="color: var(--secondary); font-weight: 600;">
                                                <i class="fas fa-external-link-alt me-1"></i> Buka di Tab Baru
                                            </a>
                                        </div>
                                        
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
                                        <div class="d-none d-md-block border rounded shadow-sm overflow-hidden" style="height: 500px; background-color: #f8f9fa;">
                                            <iframe src="{{ route('materials.view-file', $material) }}" width="100%" height="100%" style="border: none;"></iframe>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <input type="file" class="form-control" name="file" accept=".pdf" onchange="validateFileSize(this)" style="border-radius: var(--radius-sm);">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle text-primary me-1"></i> {{ ($material->file_path && !$material->hasPhysicalFile()) ? 'Silakan pilih berkas PDF untuk melengkapi materi ini.' : 'Pilih file PDF baru jika ingin mengganti file lama.' }} <strong class="text-dark">Maksimal 10 MB</strong>.</small>
                            @error('file')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- YouTube Video -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">🎥 Video YouTube (Opsional)</label>
                            <input type="url" class="form-control" name="youtube_url" value="{{ old('youtube_url', $material->youtube_url) }}" placeholder="Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ" style="border-radius: var(--radius-sm);">
                            <small class="text-muted">Masukkan link video YouTube jika ingin menyematkan video ke dalam materi</small>
                            @error('youtube_url')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2 mt-5">
                            <button class="btn btn-lg btn-primary px-4" style="border-radius: var(--radius-md);" type="submit">✓ Update Materi</button>
                            <a class="btn btn-lg btn-outline-secondary-theme" href="{{ $backUrl }}" style="border-radius: var(--radius-md);">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
function validateFileSize(input, maxMb = 10) {
    if (input.files && input.files[0]) {
        const fileSizeMb = (input.files[0].size / (1024 * 1024)).toFixed(2);
        const maxSizeMb = maxMb;
        if (input.files[0].size > maxSizeMb * 1024 * 1024) {
            alert(`⚠️ Ukuran file terlalu besar (${fileSizeMb} MB)!\n\nBatas maksimum file yang diizinkan adalah ${maxSizeMb} MB. Silakan pilih file lain.`);
            input.value = '';
        }
    }
}
</script>
@endpush
@endsection
