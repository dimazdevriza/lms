@extends('layouts.lms')

@section('title', 'Dashboard Guru')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-2">👨‍🏫 Selamat Datang, {{ Auth::user()->name }}</h1>
                    <p class="mb-0">Berikut ringkasan kegiatan mengajar Anda</p>
                </div>
                <div class="text-end">
                    <div class="small" style="color: rgba(255,255,255,0.75);">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid reveal reveal-delay-1">
        <!-- Sesi Mengajar -->
        <div class="stat-card stat-card--behavior">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Pertemuan</div>
                    <div class="stat-value stat-value--primary">{{ $meetingsCount ?? 0 }}</div>
                    <div class="stat-sub">Total sesi mengajar</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--deep">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <a href="{{ route('guru.meetings.index') }}" class="stretched-link"></a>
        </div>

        <!-- Materi Upload -->
        <div class="stat-card stat-card--attendance">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Materi Ajar</div>
                    <div class="stat-value stat-value--green">{{ $materialsCount ?? 0 }}</div>
                    <div class="stat-sub">Materi terupload</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--green">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <a href="{{ route('guru.materials.index') }}" class="stretched-link"></a>
        </div>

        <!-- Tugas Aktif -->
        <div class="stat-card stat-card--grades">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Tugas & Latihan</div>
                    <div class="stat-value" style="color: var(--accent);">{{ $assignmentsCount ?? 0 }}</div>
                    <div class="stat-sub">Tugas aktif</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--gold">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            <a href="{{ route('guru.assignments.index') }}" class="stretched-link"></a>
        </div>

        <!-- Pending Koreksi -->
        <div class="stat-card stat-card--behavior">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Belum Dinilai</div>
                    <div class="stat-value" style="color: {{ $pendingGradesCount > 0 ? '#dc3545' : 'var(--secondary)' }};">{{ $pendingGradesCount ?? 0 }}</div>
                    <div class="stat-sub">Menunggu penilaian</div>
                </div>
                <div class="stat-icon-circle" style="background: linear-gradient(135deg, {{ $pendingGradesCount > 0 ? 'rgba(220,53,69,0.12), rgba(220,53,69,0.06)' : 'rgba(67,160,71,0.12), rgba(67,160,71,0.06)' }}); color: {{ $pendingGradesCount > 0 ? '#dc3545' : 'var(--secondary)' }};">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <a href="{{ route('guru.assignments.grading', ['filter' => 'pending']) }}" class="stretched-link"></a>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="row">
        <!-- Jadwal Hari Ini & Kelas -->
        <div class="col-md-7 mb-4 reveal reveal-delay-2">
            <!-- Jadwal Mengajar Hari Ini -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <h5 class="content-card-title mb-0">Jadwal Mengajar Hari Ini</h5>
                        <span class="status-badge status-badge--hadir">{{ $todayIndo }}</span>
                    </div>
                </div>
                <div class="content-card-body">
                    @if($todaySchedules->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($todaySchedules as $schedule)
                                <div class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3 text-center" style="min-width: 75px; background: rgba(67,160,71,0.06); border-radius: var(--radius-sm);">
                                            <div class="small fw-bold" style="color: var(--secondary);">{{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '-' }}</div>
                                            <div class="small" style="color: var(--text-muted); font-size: 0.7rem;">{{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '-' }}</div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold" style="color: var(--text-heading);">{{ $schedule->subject->name ?? $schedule->activity ?? '-' }}</h6>
                                            <div class="d-flex align-items-center gap-2 small" style="color: var(--text-muted);">
                                                @if($schedule->schoolClass)
                                                    <span><i class="fas fa-door-open me-1"></i> Kelas {{ $schedule->schoolClass->name }}</span>
                                                    <span style="opacity: 0.3;">|</span>
                                                @endif
                                                <span><i class="fas fa-clock me-1"></i> {{ $schedule->slot_label }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($schedule->schoolClass && $schedule->subject)
                                        @if(isset($schedule->existingMeeting) && $schedule->existingMeeting)
                                            <a href="{{ route('guru.meetings.show', $schedule->existingMeeting) }}" class="btn btn-sm btn-success" style="border-radius: var(--radius-sm); font-weight: 600;">
                                                <i class="fas fa-check-circle me-1"></i> Pertemuan {{ $schedule->existingMeeting->number }}
                                            </a>
                                        @else
                                            <a href="{{ route('guru.meetings.class-meetings.create', ['classSlug' => $schedule->schoolClass->slug, 'subjectSlug' => $schedule->subject->slug]) }}" class="btn btn-sm btn-outline-accent-theme" style="border-radius: var(--radius-sm);">
                                                <i class="fas fa-plus me-1"></i> Buat Pertemuan
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-mug-hot"></i>
                            </div>
                            <div class="empty-state-text">
                                <strong>Tidak Ada Jadwal Mengajar</strong><br>
                                Anda tidak memiliki jadwal kelas pada hari {{ $todayIndo }} berdasarkan jadwal TU.
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Kelas & Mata Pelajaran -->
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Daftar Kelas Diampu</h5>
                </div>
                <div class="content-card-body">
                    <div class="list-group list-group-flush">
                        @forelse($assignedClasses as $major => $majorClasses)
                            <div class="mb-4">
                                <h6 class="fw-bold mb-2 text-uppercase small" style="letter-spacing: 1px; color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;">
                                    <i class="fas fa-microscope me-1"></i> JURUSAN {{ $major ?? 'UMUM' }}
                                </h6>
                                @foreach($majorClasses as $class)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom-0">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon-circle stat-icon-circle--green me-3" style="width: 36px; height: 36px; border-radius: 50%; font-size: 0.85rem;">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $class->name }}</h6>
                                                <small style="color: var(--text-muted);">{{ $class->students_count }} siswa</small>
                                            </div>
                                        </div>
                                         <div class="d-flex gap-1">
                                             <a href="{{ route('guru.classroom.show', $class) }}" class="btn btn-sm btn-outline-primary-theme" style="border-radius: 100px;">Data Siswa</a>
                                             <a href="{{ route('guru.meetings.index', ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-secondary-theme" style="border-radius: 100px;">Buka Ruang</a>
                                         </div>
                                    </div>
                                @endforeach
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-chalkboard"></i>
                                </div>
                                <div class="empty-state-text">
                                    Belum ada kelas yang diampu.<br>
                                    <small>Hubungi Tata Usaha untuk pendataan penugasan mengajar.</small>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Mini Calendar, Quick Actions + Tugas Perlu Dinilai -->
        <div class="col-md-5 mb-4 reveal reveal-delay-3">
            @php
                $calendarEvents = [];
                if (isset($todaySchedules) && $todaySchedules->isNotEmpty()) {
                    foreach ($todaySchedules as $sch) {
                        $calendarEvents[] = [
                            'date' => now()->toDateString(),
                            'title' => 'Mengajar: ' . ($sch->subject->name ?? $sch->activity ?? 'Kelas'),
                            'time' => substr($sch->start_time ?? '08:00', 0, 5),
                            'subtitle' => 'Kelas ' . ($sch->schoolClass->name ?? ''),
                            'icon' => 'fas fa-chalkboard-teacher',
                            'url' => route('guru.meetings.index')
                        ];
                    }
                }
                if (isset($recentPendingAssignments) && $recentPendingAssignments->isNotEmpty()) {
                    foreach ($recentPendingAssignments as $asg) {
                        if ($asg->due_at) {
                            $dueDate = \Carbon\Carbon::parse($asg->due_at)->toDateString();
                            $calendarEvents[] = [
                                'date' => $dueDate,
                                'title' => 'Deadline Tugas: ' . $asg->title,
                                'time' => \Carbon\Carbon::parse($asg->due_at)->format('H:i'),
                                'subtitle' => 'Kelas ' . ($asg->schoolClass?->name ?? ''),
                                'icon' => 'fas fa-file-alt',
                                'url' => route('guru.assignments.show', $asg->id)
                            ];
                        }
                    }
                }
            @endphp

            <!-- Mini Calendar Widget -->
            @include('partials.mini-calendar', ['events' => $calendarEvents])

            <!-- Aksi Cepat -->
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.06)); color: var(--accent);">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h5 class="content-card-title mb-0">Aksi Cepat</h5>
                </div>
                <div class="content-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('guru.meetings.create') }}" class="btn btn-outline-primary-theme text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-calendar-plus me-2"></i> Buat Pertemuan Baru
                        </a>
                        <a href="{{ route('guru.materials.create') }}" class="btn btn-outline-secondary-theme text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-upload me-2"></i> Upload Materi Ajar
                        </a>
                        <a href="{{ route('guru.assignments.create') }}" class="btn btn-outline-accent-theme text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-plus-circle me-2"></i> Buat Tugas / Latihan
                        </a>
                        @if($pendingGradesCount > 0)
                            <a href="{{ route('guru.assignments.grading', ['filter' => 'pending']) }}" class="btn btn-danger text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                                <i class="fas fa-check-double me-2"></i> Nilai Tugas Siswa
                                <span class="badge bg-white text-danger ms-2">{{ $pendingGradesCount }} menunggu</span>
                            </a>
                        @else
                            <a href="{{ route('guru.assignments.grading') }}" class="btn btn-outline-secondary text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                                <i class="fas fa-check-double me-2"></i> Lihat Penilaian
                            </a>
                        @endif
                        <a href="{{ route('guru.attendances.index') }}" class="btn btn-outline-primary-theme text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                            <i class="fas fa-clipboard-check me-2"></i> Catat Kehadiran Siswa
                        </a>
                        @if(auth()->user()->teacher && auth()->user()->teacher->homeroomClasses()->count() > 0)
                            <a href="{{ route('guru.classroom.index') }}" class="btn btn-outline-primary-theme text-start py-3 px-4" style="border-radius: var(--radius-sm);">
                                <i class="fas fa-users me-2"></i> Kelola Kelas Perwalian
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tugas Terbaru yang Perlu Dinilai -->
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(249, 168, 37, 0.15), rgba(249, 168, 37, 0.06)); color: var(--accent);">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-grow-1">
                        <h5 class="content-card-title mb-0">Perlu Dinilai</h5>
                        @if($pendingGradesCount > 0)
                            <a href="{{ route('guru.assignments.grading', ['filter' => 'pending']) }}" class="small text-decoration-none" style="color: var(--secondary);">
                                Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="content-card-body">
                    @if($recentPendingAssignments->isEmpty())
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="empty-state-text">Semua tugas sudah dinilai! 🎉</div>
                        </div>
                    @else
                        @foreach($recentPendingAssignments as $assignment)
                            @php
                                $ungradedCount = $assignment->submissions->whereNull('score')->count();
                            @endphp
                            <a href="{{ route('guru.assignments.show', $assignment) }}" class="d-block text-decoration-none mb-3 p-3 rounded-3" style="transition: all 0.3s var(--ease-out); background: var(--bg-body); border: 1px solid rgba(27,94,32,0.04);">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold" style="color: var(--primary); font-size: 0.9rem; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $assignment->title }}</h6>
                                        <div class="d-flex gap-2 small" style="color: var(--text-muted);">
                                            <span>{{ $assignment->schoolClass?->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span>{{ $assignment->subject?->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                    <span class="status-badge" style="background: rgba(249,168,37,0.12); color: #B26A00; font-size: 0.7rem;">{{ $ungradedCount }} belum dinilai</span>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
