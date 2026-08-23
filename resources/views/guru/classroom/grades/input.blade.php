@extends('layouts.lms')

@section('title', 'Input Nilai - ' . $class->name)

@section('content')
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 style="font-family: 'Plus Jakarta Sans', sans-serif;">📋 Input Nilai Siswa</h1>
                    <p>Kelas {{ $class->name }} • Masukkan nilai siswa untuk berbagai jenis penilaian</p>
                </div>
                <a href="{{ route('guru.classroom.grades', $class) }}" class="btn btn-outline-light d-inline-flex align-items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="content-card reveal reveal-delay-1">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <h5 class="content-card-title">Formulir Input Nilai</h5>
                </div>
                <div class="content-card-body">
                    <form method="POST" action="{{ route('guru.classroom.grades.store', $class) }}" id="gradesForm">
                        @csrf

                        <div id="gradesContainer">
                            <div class="grade-entry mb-4 p-4 border">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">👤 Siswa</label>
                                        <select class="form-select" name="grades[0][student_id]" required>
                                            <option value="">-- Pilih Siswa --</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}">
                                                    {{ $student->user?->name ?? 'Siswa (Tanpa Nama)' }} (NISN: {{ $student->nisn }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">📖 Mata Pelajaran</label>
                                        <select class="form-select" name="grades[0][subject_id]" required>
                                            <option value="">-- Pilih Mata Pelajaran --</option>
                                            @php
                                                $teacher = App\Models\Teacher::where('user_id', auth()->id())->first();
                                                $mySubjects = $teacher ? $teacher->subjects()->orderBy('name')->get() : collect();
                                                
                                                // Jika guru punya mata pelajaran diampu, gunakan itu, jika tidak tampilkan semua
                                                $subjectsToDisplay = $mySubjects->isNotEmpty() ? $mySubjects : \App\Models\Subject::orderBy('name')->get();
                                            @endphp
                                            @foreach($subjectsToDisplay as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">🎯 Jenis Penilaian</label>
                                        <input class="form-control" type="text" name="grades[0][assessment_type]" placeholder="Contoh: UTS, UAS, Tugas" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">📊 Nilai (0-100)</label>
                                        <input class="form-control" type="number" name="grades[0][score]" min="0" max="100" step="0.1" placeholder="Contoh: 85" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">📅 Tanggal</label>
                                        <input class="form-control" type="date" name="grades[0][assessment_date]" value="{{ now()->toDateString() }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Tambah Entry -->
                        <div class="mb-4">
                            <button type="button" class="btn btn-outline-primary-theme d-inline-flex align-items-center gap-2" id="addGradeBtn">
                                <i class="fas fa-plus"></i> Tambah Penilaian Lain
                            </button>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="d-flex gap-2 mt-5">
                            <button class="btn btn-primary btn-lg px-4" type="submit">
                                <i class="fas fa-check-circle"></i> Simpan Semua Nilai
                            </button>
                            <a class="btn btn-outline-secondary btn-lg px-4" href="{{ route('guru.classroom.grades', $class) }}">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .grade-entry {
            background: var(--bg-body, #FAFAF7);
            border: 1px solid rgba(27, 94, 32, 0.08) !important;
            border-radius: var(--radius-md) !important;
            transition: all 0.3s cubic-bezier(0.22, 0.61, 0.36, 1);
            position: relative;
        }
        .grade-entry:hover {
            border-color: rgba(27, 94, 32, 0.15) !important;
            box-shadow: 0 4px 16px rgba(27, 94, 32, 0.04);
        }
    </style>
    @endpush

    <script>
        // Data subjects untuk digunakan di JavaScript
        @php
            $teacher = App\Models\Teacher::where('user_id', auth()->id())->first();
            $mySubjects = $teacher ? $teacher->subjects()->orderBy('name')->get() : collect();
            $subjectsData = $mySubjects->isNotEmpty() ? $mySubjects : \App\Models\Subject::orderBy('name')->get();
        @endphp
        const subjectsData = @json($subjectsData);
        const studentsData = @json($students->map(fn($s) => ['id' => $s->id, 'name' => $s->user?->name ?? 'Siswa (Tanpa Nama)', 'nisn' => $s->nisn]));

        document.getElementById('addGradeBtn').addEventListener('click', function() {
            const container = document.getElementById('gradesContainer');
            const entries = container.querySelectorAll('.grade-entry').length;
            
            const newEntry = document.createElement('div');
            newEntry.className = 'grade-entry mb-4 p-4 border';
            
            // Build students options
            let studentsOptions = '<option value="">-- Pilih Siswa --</option>';
            studentsData.forEach(student => {
                studentsOptions += `<option value="${student.id}">${student.name} (NISN: ${student.nisn})</option>`;
            });

            // Build subjects options
            let subjectsOptions = '<option value="">-- Pilih Mata Pelajaran --</option>';
            subjectsData.forEach(subject => {
                subjectsOptions += `<option value="${subject.id}">${subject.name}</option>`;
            });

            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">👤 Siswa</label>
                        <select class="form-select" name="grades[${entries}][student_id]" required>
                            ${studentsOptions}
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">📖 Mata Pelajaran</label>
                        <select class="form-select" name="grades[${entries}][subject_id]" required>
                            ${subjectsOptions}
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">🎯 Jenis Penilaian</label>
                        <input class="form-control" type="text" name="grades[${entries}][assessment_type]" placeholder="Contoh: UTS, UAS, Tugas" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">📊 Nilai (0-100)</label>
                        <input class="form-control" type="number" name="grades[${entries}][score]" min="0" max="100" step="0.1" placeholder="Contoh: 85" required>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">📅 Tanggal</label>
                        <input class="form-control" type="date" name="grades[${entries}][assessment_date]" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>

                    <div class="col-12 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger removeGradeBtn d-inline-flex align-items-center gap-1">
                            <i class="fas fa-trash-alt"></i> Hapus Entry
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(newEntry);
            attachRemoveListener();
        });

        function attachRemoveListener() {
            document.querySelectorAll('.removeGradeBtn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.closest('.grade-entry').remove();
                });
            });
        }

        attachRemoveListener();
    </script>
@endsection
