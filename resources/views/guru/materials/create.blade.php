@extends('layouts.lms')

@section('title', 'Upload Materi')

@section('content')
    @php
        if (auth()->user()->role === 'admin') {
            $backUrl = request('meeting_id')
                ? route('admin.attendances.meetingMaterials', request('meeting_id'))
                : (request('class_id') && request('subject_id')
                    ? route('admin.attendances.showSubject', ['class' => request('class_id'), 'subject' => request('subject_id')])
                    : route('admin.attendances.index'));
        } else {
            $backUrl = request('meeting_id') ? route('guru.meetings.show', request('meeting_id')) : route('guru.materials.index');
        }
    @endphp
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary-theme btn-sm" style="border-radius: var(--radius-sm);">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">📚 Upload Materi Pembelajaran Baru</h1>
                <p class="text-muted mb-0">Bagikan materi pembelajaran kepada siswa Anda</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4 reveal reveal-delay-1">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Formulir Materi Baru</h5>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="{{ route('guru.materials.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Pertemuan, Kelas & Mata Pelajaran -->
                        <div class="row mb-4">
                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold" style="color: var(--primary);">🗓️ Pilih Pertemuan (Opsional)</label>
                                <select class="form-select select-meeting" style="border: 2px solid var(--accent); border-radius: var(--radius-sm);" name="meeting_id" id="meeting_id">
                                    <option value="">-- Tanpa Pertemuan (Materi Mandiri) --</option>
                                    @foreach($meetings as $meeting)
                                        <option value="{{ $meeting->id }}" 
                                            data-class="{{ $meeting->class_id }}" 
                                            data-subject="{{ $meeting->subject_id }}"
                                            @selected(old('meeting_id', request('meeting_id')) == $meeting->id)>
                                            Pertemuan {{ $meeting->number }}: {{ $meeting->title }} ({{ $meeting->schoolClass->name }} - {{ $meeting->subject->name }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika memilih pertemuan, Kelas dan Mata Pelajaran akan terisi otomatis.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">🎓 Kelas</label>
                                <select class="form-select" name="class_id" id="class_id" required style="border-radius: var(--radius-sm);">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" style="color: var(--primary);">📖 Mata Pelajaran</label>
                                <select class="form-select" name="subject_id" id="subject_id" required style="border-radius: var(--radius-sm);">
                                    @if($subjects->count() > 1)
                                        <option value="">-- Pilih Mata Pelajaran --</option>
                                    @endif
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id || $subjects->count() == 1)>{{ $subject->name }}</option>
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
                                
                                // Run on load in case of pre-selected meeting
                                if (meetingSelect.value) {
                                    updateFields();
                                }
                            });
                        </script>

                        <!-- Judul Materi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📝 Judul Materi</label>
                            <input class="form-control" name="title" value="{{ old('title') }}" placeholder="Contoh: Bab 5 - Persamaan Kuadrat" required style="border-radius: var(--radius-sm);">
                            @error('title')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Konten -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📄 Konten Materi (Opsional)</label>
                            <textarea class="form-control" name="content" placeholder="Tuliskan konten materi pembelajaran di sini..." rows="6" style="border-radius: var(--radius-sm);">{{ old('content') }}</textarea>
                            <small class="text-muted">Anda dapat menuliskan teks materi secara langsung di sini</small>
                            @error('content')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- File PDF -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📤 File PDF Materi</label>
                            <input type="file" class="form-control" name="file" accept=".pdf" onchange="validateFileSize(this)" style="border-radius: var(--radius-sm);">
                            <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle text-primary me-1"></i> Pilih file PDF materi jika ada. <strong class="text-dark">Maksimal 10 MB</strong>.</small>
                            @error('file')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- YouTube Video -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">🎥 Video YouTube (Opsional)</label>
                            <input type="url" class="form-control" name="youtube_url" value="{{ old('youtube_url') }}" placeholder="Contoh: https://www.youtube.com/watch?v=dQw4w9WgXcQ" style="border-radius: var(--radius-sm);">
                            <small class="text-muted">Masukkan link video YouTube jika ingin menyematkan video ke dalam materi</small>
                            @error('youtube_url')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>
                        <!-- Buttons -->
                        <div class="d-flex gap-2 mt-5">
                            <button class="btn btn-lg btn-primary px-4" style="border-radius: var(--radius-md);" type="submit">✓ Upload Materi</button>
                            <a class="btn btn-lg btn-outline-secondary-theme" href="{{ request('meeting_id') ? route('guru.meetings.show', request('meeting_id')) : route('guru.materials.index') }}" style="border-radius: var(--radius-md);">Batal</a>
                        </div>
                    </form>
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

        <!-- Info Sidebar -->
        <div class="col-lg-4 mb-4 reveal reveal-delay-2">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(249,168,37,0.15), rgba(249,168,37,0.06)); color: var(--accent);">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Tips Upload Materi</h5>
                </div>
                <div class="content-card-body">
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Gunakan judul yang deskriptif dan mudah dipahami</li>
                        <li class="mb-2">Jelaskan tujuan dan topik pembelajaran</li>
                        <li class="mb-2">Gunakan format yang rapi dan terstruktur</li>
                        <li class="mb-2">Tambahkan referensi atau sumber bacaan</li>
                        <li class="mb-2">Pastikan konten sesuai dengan kurikulum</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
