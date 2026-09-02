@extends('layouts.lms')

@section('title', 'Edit Absensi')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('guru.attendances.show', $attendance->id) }}" class="btn btn-outline-secondary-theme btn-sm" style="border-radius: var(--radius-sm);">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="mb-1 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.75rem;">✏️ Edit Kehadiran Siswa</h1>
                @if($attendance->meeting)
                    <p class="text-muted mb-0">Pertemuan ke-{{ $attendance->meeting->number }}: {{ $attendance->meeting->title }} | {{ $attendance->schoolClass->name }}</p>
                @else
                    <p class="text-muted mb-0">Mata Pelajaran: {{ $attendance->subject->name }} | {{ $attendance->schoolClass->name }}</p>
                @endif
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('guru.attendances.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Left Config Sidebar -->
            <div class="col-lg-4 mb-4 reveal reveal-delay-1">
                <div class="content-card mb-4">
                    <div class="content-card-header">
                        <div class="content-card-header-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5 class="content-card-title mb-0">Detail Absensi</h5>
                    </div>
                    <div class="content-card-body">
                        <!-- Tanggal -->
                        <div class="mb-4">
                            <label class="form-label fw-bold" style="color: var(--primary);">📅 Tanggal</label>
                            <input class="form-control" type="date" name="date" value="{{ old('date', $attendance->date) }}" required style="border-radius: var(--radius-sm);">
                            @error('date')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-lg btn-primary" style="border-radius: var(--radius-md);" type="submit">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                    <a class="btn btn-outline-secondary-theme btn-lg" href="{{ route('guru.attendances.show', $attendance->id) }}" style="border-radius: var(--radius-md);">Batal</a>
                </div>
            </div>

            <!-- Right Student List -->
            <div class="col-lg-8 mb-4 reveal reveal-delay-2">
                <div class="content-card">
                    <div class="content-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="content-card-header-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="content-card-title mb-0">Daftar Siswa</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary-theme" id="markAllHadir" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-check-double me-1"></i> Semua Hadir
                        </button>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive" id="studentTableWrapper">
                             <!-- Search Input for Students -->
                             <div class="mb-3">
                                 <div class="input-group">
                                     <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: var(--radius-sm) 0 0 var(--radius-sm); border-color: rgba(27,94,32,0.12);">
                                         <i class="fas fa-search"></i>
                                     </span>
                                     <input type="text" id="studentSearch" class="form-control border-start-0 ps-0" placeholder="Cari nama siswa..." style="border-radius: 0 var(--radius-sm) var(--radius-sm) 0; border-color: rgba(27,94,32,0.12);">
                                 </div>
                             </div>
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700; width: 60px;">No</th>
                                        <th class="text-muted text-uppercase" style="font-size: 0.75rem; font-weight: 700;">Nama Siswa</th>
                                        <th class="text-muted text-uppercase text-center" style="font-size: 0.75rem; font-weight: 700; width: 300px;">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody id="studentTableBody">
                                    <!-- Students loaded via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <style>
        .status-radio-group {
            display: flex;
            gap: 6px;
            justify-content: center;
        }
        .status-radio-item {
            flex: 1;
        }
        .status-radio-item input[type="radio"] {
            display: none;
        }
        .status-radio-item label {
            display: block;
            padding: 8px 4px;
            text-align: center;
            border: 1px solid rgba(27, 94, 32, 0.1);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.2s cubic-bezier(0.22, 0.61, 0.36, 1);
            background: var(--bg-body);
            color: var(--text-muted);
        }
        
        /* Hadir */
        .status-radio-item input[value="hadir"]:checked + label {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(27, 94, 32, 0.15);
        }
        /* Izin */
        .status-radio-item input[value="izin"]:checked + label {
            background-color: var(--accent);
            color: #4E3400;
            border-color: var(--accent);
            box-shadow: 0 4px 12px rgba(249, 168, 37, 0.15);
        }
        /* Sakit */
        .status-radio-item input[value="sakit"]:checked + label {
            background-color: var(--secondary);
            color: white;
            border-color: var(--secondary);
            box-shadow: 0 4px 12px rgba(67, 160, 71, 0.15);
        }
        /* Alpa */
        .status-radio-item input[value="alpa"]:checked + label {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
        }
        /* Cabut */
        .status-radio-item input[value="cabut"]:checked + label {
            background-color: #fd7e14;
            color: white;
            border-color: #fd7e14;
            box-shadow: 0 4px 12px rgba(253, 126, 20, 0.15);
        }
        
        .status-radio-item label:hover {
            background-color: rgba(27, 94, 32, 0.04);
            transform: translateY(-1px);
        }
    </style>

    <script>
        const classesData = @json($classes);
        const studentStatuses = @json($studentStatuses);
        const classId = '{{ $attendance->class_id }}';
        
        const studentTableBody = document.getElementById('studentTableBody');
        const markAllHadirBtn = document.getElementById('markAllHadir');

        function loadStudents() {
            const selectedClass = classesData.find(c => c.id == classId);
            if (selectedClass && selectedClass.students) {
                const sortedStudents = [...selectedClass.students].sort((a, b) => {
                    const nameA = (a.user && a.user.name) ? a.user.name.toLowerCase() : '';
                    const nameB = (b.user && b.user.name) ? b.user.name.toLowerCase() : '';
                    return nameA.localeCompare(nameB, undefined, { sensitivity: 'base' });
                });

                studentTableBody.innerHTML = '';

                sortedStudents.forEach((student, index) => {
                    // Check previous status
                    const currentStatus = studentStatuses[student.id] || 'hadir';
                    
                    const row = `
                        <tr>
                            <td class="text-muted fw-bold">${index + 1}</td>
                            <td>
                                <div class="fw-bold text-dark">${student.user ? student.user.name : 'Siswa Tanpa Akun User'}</div>
                                <div class="text-muted" style="font-size: 0.8rem;">NISN: ${student.nisn}</div>
                            </td>
                            <td>
                                <div class="status-radio-group">
                                    <div class="status-radio-item">
                                        <input type="radio" name="statuses[${student.id}]" id="h_${student.id}" value="hadir" ${currentStatus === 'hadir' ? 'checked' : ''} required>
                                        <label for="h_${student.id}">HADIR</label>
                                    </div>
                                    <div class="status-radio-item">
                                        <input type="radio" name="statuses[${student.id}]" id="i_${student.id}" value="izin" ${currentStatus === 'izin' ? 'checked' : ''} required>
                                        <label for="i_${student.id}">IZIN</label>
                                    </div>
                                    <div class="status-radio-item">
                                        <input type="radio" name="statuses[${student.id}]" id="s_${student.id}" value="sakit" ${currentStatus === 'sakit' ? 'checked' : ''} required>
                                        <label for="s_${student.id}">SAKIT</label>
                                    </div>
                                    <div class="status-radio-item">
                                        <input type="radio" name="statuses[${student.id}]" id="a_${student.id}" value="alpa" ${currentStatus === 'alpa' ? 'checked' : ''} required>
                                        <label for="a_${student.id}">ALPA</label>
                                    </div>
                                    <div class="status-radio-item">
                                        <input type="radio" name="statuses[${student.id}]" id="c_${student.id}" value="cabut" ${currentStatus === 'cabut' ? 'checked' : ''} required>
                                        <label for="c_${student.id}">CABUT</label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                    studentTableBody.insertAdjacentHTML('beforeend', row);
                });
            }
        }

        markAllHadirBtn.addEventListener('click', () => {
            const radioButtons = document.querySelectorAll('input[type="radio"][value="hadir"]');
            radioButtons.forEach(radio => radio.checked = true);
        });

        const studentSearch = document.getElementById('studentSearch');
        if (studentSearch) {
            studentSearch.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
            studentSearch.addEventListener('input', function(e) {
                const query = e.target.value.toLowerCase().trim();
                const rows = studentTableBody.querySelectorAll('tr');
                rows.forEach(row => {
                    const nameCell = row.querySelector('.fw-bold.text-dark');
                    if (nameCell) {
                        const name = nameCell.textContent.toLowerCase();
                        if (name.includes(query)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            });
        }

        // Initialize students list
        document.addEventListener('DOMContentLoaded', loadStudents);
    </script>
@endsection
