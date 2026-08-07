@extends('layouts.lms')

@section('title', 'Dashboard Siswa')

@section('content')
    <!-- Header Banner -->
    <div class="page-header-banner reveal">
        <div class="page-header-banner-inner">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1"> Selamat Datang, {{ Auth::user()->name }}</h1>
                    <p>Pantau progres belajar Anda di sini</p>
                </div>
                <div class="text-end">
                    <div class="small" style="color: rgba(255,255,255,0.7);">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </div>
                    @if(Auth::user()->student && Auth::user()->student->schoolClass)
                        <div class="mt-2 d-flex gap-2 justify-content-end">
                            <span class="badge">
                                {{ Auth::user()->student->schoolClass->name }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Pelajaran Hari Ini -->
    <div class="content-card mb-4 reveal reveal-delay-1" style="border-radius: var(--radius-md) !important;">
        <div class="content-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="content-card-header-icon" style="background: linear-gradient(135deg, rgba(37, 103, 30, 0.1), rgba(37, 103, 30, 0.05)); color: var(--primary);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h5 class="content-card-title mb-0">Jadwal Pelajaran Hari Ini</h5>
            </div>
            <span class="badge bg-light text-muted border" style="font-size: 0.75rem;">
                Hari {{ $todayDayName }}
            </span>
        </div>
        <div class="content-card-body p-4">
            @if($todaySchedules->isNotEmpty())
                <div class="row row-cols-1 row-cols-md-2 g-3">
                    @foreach($todaySchedules as $schedule)
                        <div class="col">
                            <div class="p-3 border rounded-3 h-100 bg-light-subtle d-flex flex-column justify-content-between">
                                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap flex-sm-nowrap">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-flex flex-column align-items-center justify-content-center p-2 rounded-3 text-center flex-shrink-0" style="min-width: 80px; height: 60px; background: rgba(27, 94, 32, 0.07); border: 1px solid rgba(27, 94, 32, 0.15);">
                                            <span class="fw-bold" style="font-size: 0.85rem; color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;">{{ substr($schedule->start_time ?? $schedule->timeSlot?->start_time ?? '', 0, 5) }}</span>
                                            <div style="width: 16px; height: 1.5px; background-color: rgba(27, 94, 32, 0.25); margin: 2px 0;"></div>
                                            <span class="fw-semibold" style="font-size: 0.75rem; color: var(--text-body);">{{ substr($schedule->end_time ?? $schedule->timeSlot?->end_time ?? '', 0, 5) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block" style="font-size: 0.95rem; font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.3;">{{ $schedule->subject->name ?? $schedule->activity ?? '-' }}</span>
                                            @if($schedule->subject)
                                            <small class="text-muted" style="font-size: 0.78rem;">
                                                <i class="far fa-user me-1"></i> {{ $schedule->teacher->user->name ?? 'Tidak ada guru' }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2 mt-sm-0 align-self-start align-self-sm-center">
                                        <span class="badge rounded-pill px-2.5 py-1.5 fw-bold" style="font-size: 0.72rem; background-color: rgba(27, 94, 32, 0.1); color: var(--primary); border: 1px solid rgba(27, 94, 32, 0.18); white-space: normal; text-align: left;">
                                            <i class="fas fa-clock me-1" style="font-size: 0.7rem;"></i>{{ $schedule->slot_label ?? $schedule->timeSlot?->label ?? '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <div class="text-muted mb-2" style="font-size: 2.5rem;"><i class="far fa-calendar-times"></i></div>
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">Tidak ada jadwal pelajaran hari ini (Hari Libur).</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Metrics Section -->
    <div class="stats-grid reveal reveal-delay-2">
        <!-- Tugas Disubmit -->
        <div class="stat-card stat-card--behavior">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Tugas Disubmit</div>
                    <div class="stat-value stat-value--green">{{ $submissionCount ?? 0 }}</div>
                    <div class="stat-sub">Tugas dikumpulkan</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--green">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
            <a href="{{ route('siswa.assignments.index') }}" class="stretched-link"></a>
        </div>

        <!-- Materi Tersedia -->
        <div class="stat-card stat-card--attendance">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Materi Pembelajaran</div>
                    <div class="stat-value stat-value--primary">{{ $materialsCount ?? 0 }}</div>
                    <div class="stat-sub">Materi tersedia</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--deep">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <a href="{{ route('siswa.subjects.index') }}" class="stretched-link"></a>
        </div>

        <!-- Kehadiran -->
        <div class="stat-card stat-card--grades">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Kehadiran Harian</div>
                    <div class="stat-value stat-value--green">{{ $attendanceRate }}%</div>
                    <div class="stat-sub">Kehadiran bulan ini</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--gold">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <a href="{{ route('siswa.attendance.index') }}" class="stretched-link"></a>
        </div>

        <!-- Nilai Rata-rata -->
        <div class="stat-card stat-card--grades">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Rata-Rata Nilai</div>
                    <div class="stat-value stat-value--primary">{{ $averageGrade }}</div>
                    <div class="stat-sub">Nilai tugas aktif</div>
                </div>
                <div class="stat-icon-circle stat-icon-circle--gold">
                    <i class="fas fa-star"></i>
                </div>
            </div>
            <a href="{{ route('siswa.assignments.index') }}" class="stretched-link"></a>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="row">
        <!-- Tugas Terbaru Section -->
        <div class="col-md-7 mb-4 reveal reveal-delay-3">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h5 class="content-card-title">Tugas Terbaru</h5>
                </div>
                <div class="content-card-body">
                    <div class="list-group list-group-flush">
                        @forelse($recentAssignments as $assignment)
                            <div class="list-group-item d-flex justify-content-between align-items-start px-0 py-3 border-bottom">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold" style="color: var(--text-heading);">{{ $assignment->subject ? $assignment->subject->name : 'N/A' }} - {{ $assignment->title }}</h6>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i> Deadline: {{ $assignment->due_at }}</small>
                                </div>
                                <span class="status-badge status-badge--aktif">Aktif</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="empty-state-text">Tidak ada tugas terbaru.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Mini Calendar & Quick Actions -->
        <div class="col-md-5 mb-4 reveal reveal-delay-3">
            @php
                $calendarEvents = [];
                if (isset($todaySchedules) && $todaySchedules->isNotEmpty()) {
                    foreach ($todaySchedules as $sch) {
                        $calendarEvents[] = [
                            'date' => now()->toDateString(),
                            'title' => $sch->subject->name ?? $sch->activity ?? 'Jadwal Kelas',
                            'time' => substr($sch->start_time ?? $sch->timeSlot?->start_time ?? '08:00', 0, 5),
                            'subtitle' => $sch->slot_label ?? 'Jadwal Pelajaran',
                            'icon' => 'fas fa-book-open',
                            'url' => route('siswa.subjects.index')
                        ];
                    }
                }
                if (isset($recentAssignments) && $recentAssignments->isNotEmpty()) {
                    foreach ($recentAssignments as $asg) {
                        if ($asg->due_at) {
                            $dueDate = \Carbon\Carbon::parse($asg->due_at)->toDateString();
                            $calendarEvents[] = [
                                'date' => $dueDate,
                                'title' => 'Tenggat: ' . $asg->title,
                                'time' => \Carbon\Carbon::parse($asg->due_at)->format('H:i'),
                                'subtitle' => $asg->subject?->name ?? 'Tugas Kelas',
                                'icon' => 'fas fa-tasks',
                                'url' => route('siswa.assignments.show', $asg->id)
                            ];
                        }
                    }
                }
            @endphp

            <!-- Mini Calendar Widget -->
            @include('partials.mini-calendar', ['events' => $calendarEvents])

            <div class="content-card mb-4">
                <div class="content-card-header">
                    <div class="content-card-header-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h5 class="content-card-title">Aksi Cepat</h5>
                </div>
                <div class="content-card-body">
                    <div class="d-grid gap-3">
                        <a href="{{ route('siswa.assignments.index') }}" class="btn btn-outline-primary-theme w-100">
                            <i class="fas fa-file-alt"></i> Lihat Semua Tugas
                        </a>
                        <a href="{{ route('siswa.subjects.index') }}" class="btn btn-outline-secondary-theme w-100">
                            <i class="fas fa-book"></i> Lihat Mata Pelajaran
                        </a>
                        <a href="{{ route('siswa.attendance.index') }}" class="btn btn-outline-accent-theme w-100">
                            <i class="fas fa-clipboard-user"></i> Lihat Kehadiran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


