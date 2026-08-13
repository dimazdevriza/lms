<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'LMS SMA 15 Padang')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#134611">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/images/logo-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/v4-shims.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- TomSelect CSS for beautiful dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <!-- Main LMS CSS stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/lms.css') }}?v={{ time() }}">
    <!-- KaTeX CSS for rendering math formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    @stack('styles')
</head>
<body class="{{ session()->has('impersonate_original_id') ? 'has-impersonate-banner' : '' }}">
    @if(session()->has('impersonate_original_id'))
        <div class="impersonate-banner d-flex align-items-center justify-content-between px-3 px-md-4 py-2 text-white" style="background: linear-gradient(135deg, #e65100, #ff8f00) !important; color: #ffffff !important;">
            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                <i class="fas fa-user-secret text-white flex-shrink-0"></i>
                <span class="d-none d-sm-inline">Anda sedang login sebagai <strong>{{ auth()->user()->name }}</strong> (Role: {{ strtoupper(auth()->user()->role) }}). Anda melihat data persis seperti yang mereka lihat.</span>
                <span class="d-inline d-sm-none text-truncate fw-semibold" style="font-size: 0.8rem;">Menyamar: <strong>{{ auth()->user()->name }}</strong></span>
            </div>
            <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0 flex-shrink-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-dark fw-bold px-2 px-sm-3 py-1 shadow-sm" style="border-radius: var(--radius-sm); border: none; font-size: 0.8rem; white-space: nowrap;">
                    <span class="d-none d-sm-inline">🔌 Kembali ke Admin</span>
                    <span class="d-inline d-sm-none">🔌 Admin</span>
                </button>
            </form>
        </div>
    @endif
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light flex-nowrap">
        <div class="d-flex align-items-center flex-grow-1">
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}">
                {{-- ponytail: school logo replacement --}}
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" style="height: 32px; width: 32px; border-radius: 50%; object-fit: cover; background: #fff; padding: 2px; border: 1px solid rgba(19, 70, 17, 0.15);">
                <span class="d-none d-sm-inline">LMS SMA 15&nbsp;Padang</span>
                <span class="d-inline d-sm-none fw-bold ms-1" style="font-size: 0.95rem;">LMS</span>
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            @guest
                <a class="nav-link ms-2" href="{{ route('login') }}">Login</a>
            @else

                @php
                    $unreadNotificationsCount = \App\Models\Notification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->count();
                    $headerNotifications = \App\Models\Notification::where('user_id', auth()->id())
                        ->orderByDesc('created_at')
                        ->take(8)
                        ->get();
                @endphp

                <!-- Notification Dropdown Popup -->
                <div class="dropdown me-1">
                    <button class="nav-icon-btn position-relative border-0 no-loader p-0 shadow-none bg-transparent" 
                            type="button" 
                            id="notificationDropdown" 
                            data-bs-toggle="dropdown" 
                            data-bs-auto-close="outside"
                            aria-expanded="false" 
                            title="Notifikasi">
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: rgba(19, 70, 17, 0.06); border: 1px solid rgba(19, 70, 17, 0.08); color: var(--primary);">
                            <i class="fas fa-bell"></i>
                        </div>
                        <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadNotificationsCount > 0 ? '' : 'd-none' }}" style="font-size: 0.6rem; padding: 0.2em 0.45em; margin-top: 4px; margin-left: -6px;">
                            {{ $unreadNotificationsCount }}
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 notification-popup-menu" 
                         aria-labelledby="notificationDropdown" 
                         style="width: 360px; max-width: 92vw; border-radius: 16px; overflow: hidden; margin-top: 10px;">
                        
                        <!-- Header Popup -->
                        <div class="d-flex align-items-center justify-content-between px-3 py-2.5 bg-white border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-bell text-warning"></i>
                                <span class="fw-bold text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.95rem;">Notifikasi</span>
                                <span id="notificationHeaderBadge" class="badge bg-danger-subtle text-danger rounded-pill px-2 py-0.5 fw-semibold {{ $unreadNotificationsCount > 0 ? '' : 'd-none' }}" style="font-size: 0.7rem;">
                                    <span id="notificationUnreadCountText">{{ $unreadNotificationsCount }}</span> baru
                                </span>
                            </div>
                            <button type="button" 
                                    id="btnMarkAllRead" 
                                    onclick="markAllNotificationsRead(event)" 
                                    class="btn btn-link text-decoration-none p-0 text-success fw-semibold border-0 bg-transparent {{ $unreadNotificationsCount > 0 ? '' : 'd-none' }}" 
                                    style="font-size: 0.78rem;">
                                <i class="fas fa-check-double me-1"></i> Tandai Dibaca
                            </button>
                        </div>

                        <!-- Scrollable Notification List -->
                        <div id="notificationListBody" class="notification-list-body" style="max-height: 360px; overflow-y: auto;">
                            @forelse($headerNotifications as $n)
                                <a href="{{ route('notifications.read', $n) }}" 
                                   onclick="handleNotificationClick(event, '{{ route('notifications.read', $n) }}', '{{ $n->url ?? '' }}', {{ $n->id }})"
                                   class="dropdown-item p-3 text-wrap d-flex align-items-start gap-2.5 position-relative border-bottom notification-item-{{ $n->id }}"
                                   style="{{ is_null($n->read_at) ? 'background-color: rgba(37, 103, 30, 0.04) !important;' : '' }} transition: background-color 0.2s;">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 mt-0.5"
                                         style="width: 34px; height: 34px; background-color: {{ is_null($n->read_at) ? 'var(--primary)' : '#6c757d' }}; font-size: 0.8rem;">
                                        @if(str_contains(strtolower($n->title), 'nilai') || str_contains(strtolower($n->title), 'score'))
                                            <i class="fas fa-star"></i>
                                        @elseif(str_contains(strtolower($n->title), 'tugas') || str_contains(strtolower($n->title), 'assignment'))
                                            <i class="fas fa-tasks"></i>
                                        @elseif(str_contains(strtolower($n->title), 'materi'))
                                            <i class="fas fa-book"></i>
                                        @else
                                            <i class="fas fa-bell"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden" style="line-height: 1.3;">
                                        <div class="d-flex justify-content-between align-items-baseline gap-1 mb-1">
                                            <span class="fw-bold text-dark text-truncate d-block" style="font-size: 0.84rem; max-width: 190px;" title="{{ $n->title }}">
                                                {{ $n->title }}
                                            </span>
                                            <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">
                                                {{ $n->created_at ? $n->created_at->diffForHumans() : '' }}
                                            </small>
                                        </div>
                                        <p class="text-muted mb-0 small text-truncate-2" style="font-size: 0.78rem; line-height: 1.35;">
                                            {{ $n->message }}
                                        </p>
                                    </div>
                                    @if(is_null($n->read_at))
                                        <span class="position-absolute end-0 top-50 translate-middle-y me-2 p-1 bg-success border border-light rounded-circle unread-dot" 
                                              style="width: 8px; height: 8px;" title="Belum dibaca"></span>
                                    @endif
                                </a>
                            @empty
                                <div class="py-4 px-3 text-center text-muted empty-notifications-notice">
                                    <i class="fas fa-bell-slash fa-2x mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-semibold small">Belum ada notifikasi</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Pemberitahuan akademik akan muncul di sini</small>
                                </div>
                            @endforelse
                        </div>

                        <!-- Footer Link -->
                        <div class="p-2 bg-light text-center border-top">
                            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-link text-success fw-bold text-decoration-none py-1 px-3 w-100" style="font-size: 0.8rem;">
                                Lihat Semua Notifikasi <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                {{-- ponytail: dropdown profile menu --}}
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle d-flex align-items-center gap-2 shadow-none border-0 p-1 text-decoration-none" 
                            type="button" id="userMenuButton" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                        <div class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold overflow-hidden" 
                             style="width: 34px; height: 34px; font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; background: #134611;">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            @endif
                        </div>
                        <span class="d-none d-md-inline fw-semibold text-dark" style="font-size: 0.875rem;">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userMenuButton" style="border-radius: var(--radius-md);">
                        <li class="px-3 py-2 border-bottom">
                            <span class="d-block fw-bold text-dark" style="font-size: 0.85rem;">{{ auth()->user()->name }}</span>
                            <small class="text-muted" style="font-size: 0.75rem;">{{ ucfirst(auth()->user()->role) }}</small>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('profile.edit') }}" style="font-size: 0.9rem;">
                                <i class="fas fa-user-gear text-primary opacity-75"></i> Pengaturan Profil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" style="font-size: 0.9rem; border: none; background: none; width: 100%; text-align: left;">
                                    <i class="fas fa-right-from-bracket"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endguest
        </div>
    </nav>

    <!-- Sidebar -->
    @auth
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav">
                @if(auth()->user()->role === 'admin')
                    <li class="sidebar-header">Utama</li>
                    <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    
                    <li class="sidebar-header">Manajemen User</li>
                    <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><i class="fas fa-users"></i> Data User</a></li>
                    
                    <li class="sidebar-header">Akademik</li>
                    <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Tahun Ajaran</a></li>
                    <li><a href="{{ route('admin.classes.index') }}" class="{{ request()->routeIs('admin.classes.*') ? 'active' : '' }}"><i class="fas fa-door-open"></i> Data Kelas</a></li>
                    <li><a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book-open"></i> Mata Pelajaran</a></li>
                    
                    <li class="sidebar-header">Sistem</li>
                    <li><a href="{{ route('admin.monitoring.index') }}" class="{{ request()->routeIs('admin.monitoring.*') ? 'active' : '' }}"><i class="fas fa-eye"></i> Monitoring</a></li>

                @elseif(auth()->user()->role === 'tatausaha')
                    <li class="sidebar-header">Utama</li>
                    <li><a href="{{ route('tatausaha.dashboard') }}" class="{{ request()->routeIs('tatausaha.dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    
                    <li class="sidebar-header">Master Data</li>
                    <li><a href="{{ route('tatausaha.students.index') }}" class="{{ request()->routeIs('tatausaha.students.*') ? 'active' : '' }}"><i class="fas fa-graduation-cap"></i> Data Siswa</a></li>
                    <li><a href="{{ route('tatausaha.teachers.index') }}" class="{{ request()->routeIs('tatausaha.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-user"></i> Data Guru</a></li>
                    <li><a href="{{ route('tatausaha.teaching-assignments.index') }}" class="{{ request()->routeIs('tatausaha.teaching-assignments.*') ? 'active' : '' }}"><i class="fas fa-chalkboard"></i> Penugasan Guru</a></li>
                    
                    <li class="sidebar-header">Penjadwalan</li>
                    <li><a href="{{ route('tatausaha.schedules.index') }}" class="{{ request()->routeIs('tatausaha.schedules.*') ? 'active' : '' }}"><i class="fas fa-calendar-week"></i> Jadwal Pelajaran</a></li>
                    
                    <li class="sidebar-header">Laporan</li>
                    <li><a href="{{ route('tatausaha.reports.index') }}" class="{{ request()->routeIs('tatausaha.reports.*') ? 'active' : '' }}"><i class="fas fa-file-pdf"></i> Laporan Akademik</a></li>

                @elseif(auth()->user()->role === 'guru')
                    <li class="sidebar-header">Utama</li>
                    <li><a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}"><i class="fas fa-home"></i> Dashboard</a></li>
                    
                    <li class="sidebar-header">Kegiatan Mengajar</li>
                    <li><a href="{{ route('guru.meetings.index') }}" class="{{ request()->routeIs('guru.meetings.*') ? 'active' : '' }}"><i class="fas fa-calendar-alt"></i> Pertemuan Kelas</a></li>
                    <li><a href="{{ route('guru.materials.index') }}" class="{{ request()->routeIs('guru.materials.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Materi Ajar</a></li>
                    <li><a href="{{ route('guru.assignments.index') }}" class="{{ request()->routeIs('guru.assignments.index') || request()->routeIs('guru.assignments.create') || request()->routeIs('guru.assignments.edit') || request()->routeIs('guru.assignments.show') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Tugas & Latihan</a></li>
                    <li>
                        <a href="{{ route('guru.assignments.grading') }}" class="{{ request()->routeIs('guru.assignments.grading') ? 'active' : '' }}">
                            <i class="fas fa-check-double"></i> Penilaian
                            @php
                                $pendingCount = \App\Models\AssignmentSubmission::whereHas('assignment', function($q) {
                                    $q->where('teacher_id', auth()->user()->teacher?->id);
                                })->whereNull('score')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge rounded-pill ms-auto" style="background-color: #dc3545; font-size: 0.65rem;">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li><a href="{{ route('guru.attendances.index') }}" class="{{ request()->routeIs('guru.attendances.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Absensi</a></li>

                    @if(auth()->user()->teacher && auth()->user()->teacher->homeroomClasses()->count() > 0)
                        <li class="sidebar-header">Wali Kelas</li>
                        <li><a href="{{ route('guru.classroom.index') }}" class="{{ request()->routeIs('guru.classroom.*') ? 'active' : '' }}"><i class="fas fa-users"></i> Kelola Kelas Perwalian</a></li>
                    @endif

                @elseif(auth()->user()->role === 'siswa')
                    <li class="sidebar-header">Utama</li>
                    <li><a href="{{ route('siswa.dashboard') }}" class="{{ request()->routeIs('siswa.dashboard') ? 'active' : '' }}"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                    
                    <li class="sidebar-header">Akademik</li>
                    <li><a href="{{ route('siswa.subjects.index') }}" class="{{ request()->routeIs('siswa.subjects.*') || request()->routeIs('siswa.meetings.*') ? 'active' : '' }}"><i class="fas fa-book-open"></i> Mata Pelajaran</a></li>
                    <li>
                        <a href="{{ route('siswa.assignments.index') }}" class="{{ request()->routeIs('siswa.assignments.*') ? 'active' : '' }}">
                            <i class="fas fa-tasks"></i> Daftar Tugas
                            @php
                                $studentForBadge = \App\Models\Student::where('user_id', auth()->id())->first();
                                $activePendingTasksCount = 0;
                                if ($studentForBadge) {
                                    $submittedIds = \App\Models\AssignmentSubmission::where('student_id', $studentForBadge->id)->pluck('assignment_id');
                                    $activePendingTasksCount = \App\Models\Assignment::where('class_id', $studentForBadge->class_id)
                                        ->whereNotIn('id', $submittedIds)
                                        ->where(function($q) {
                                            $q->whereNull('due_at')->orWhere('due_at', '>=', now());
                                        })->count();
                                }
                            @endphp
                            @if($activePendingTasksCount > 0)
                                <span class="badge rounded-pill ms-auto" style="background-color: #f9a825; color: #fff; font-size: 0.65rem;">{{ $activePendingTasksCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li><a href="{{ route('siswa.attendance.index') }}" class="{{ request()->routeIs('siswa.attendance.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Riwayat Kehadiran</a></li>
                    <li><a href="{{ route('siswa.directory') }}" class="{{ request()->routeIs('siswa.directory') ? 'active' : '' }}"><i class="fas fa-users"></i> Teman Kelas & Guru</a></li>
                @endif
            </ul>
        </aside>
    @endauth

    <!-- Main Content -->
    <div class="main-wrapper">
        @auth
            <div class="sidebar-placeholder"></div>
        @endauth
        <div class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>Terjadi Kesalahan!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Skeleton Page Transition Loader -->
            <div id="skeleton-loader" class="skeleton-wrapper">
                <!-- Header Skeleton -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="skeleton-item skeleton-circle"></div>
                    <div class="flex-grow-1">
                        <div class="skeleton-item skeleton-title"></div>
                        <div class="skeleton-item skeleton-subtitle"></div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Card Skeleton -->
                        <div class="skeleton-item skeleton-card"></div>
                        
                        <!-- Lines Skeletons -->
                        <div class="card p-4 border-0 shadow-sm mb-4">
                            <div class="skeleton-item skeleton-line w-90"></div>
                            <div class="skeleton-item skeleton-line w-80"></div>
                            <div class="skeleton-item skeleton-line w-70"></div>
                            <div class="skeleton-item skeleton-line w-50"></div>
                        </div>
                        
                        <!-- Table/Row Skeletons -->
                        <div class="card p-4 border-0 shadow-sm">
                            <div class="skeleton-table-row">
                                <div class="skeleton-item skeleton-circle" style="width: 32px; height: 32px;"></div>
                                <div class="flex-grow-1 d-flex flex-column gap-2 justify-content-center">
                                    <div class="skeleton-item skeleton-line w-30" style="margin-bottom: 0;"></div>
                                    <div class="skeleton-item skeleton-line w-50" style="margin-bottom: 0;"></div>
                                </div>
                            </div>
                            <div class="skeleton-table-row">
                                <div class="skeleton-item skeleton-circle" style="width: 32px; height: 32px;"></div>
                                <div class="flex-grow-1 d-flex flex-column gap-2 justify-content-center">
                                    <div class="skeleton-item skeleton-line w-30" style="margin-bottom: 0;"></div>
                                    <div class="skeleton-item skeleton-line w-50" style="margin-bottom: 0;"></div>
                                </div>
                            </div>
                            <div class="skeleton-table-row border-0">
                                <div class="skeleton-item skeleton-circle" style="width: 32px; height: 32px;"></div>
                                <div class="flex-grow-1 d-flex flex-column gap-2 justify-content-center">
                                    <div class="skeleton-item skeleton-line w-30" style="margin-bottom: 0;"></div>
                                    <div class="skeleton-item skeleton-line w-50" style="margin-bottom: 0;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Sidebar Card Skeleton -->
                        <div class="card p-4 border-0 shadow-sm mb-4">
                            <div class="skeleton-item skeleton-title w-50 mb-3" style="height: 24px;"></div>
                            <div class="skeleton-item skeleton-line w-90"></div>
                            <div class="skeleton-item skeleton-line w-80"></div>
                            <div class="skeleton-item skeleton-line w-90"></div>
                            <div class="skeleton-item skeleton-line w-70"></div>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- TomSelect JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        // Inisialisasi TomSelect pada semua elemen yang memiliki class .ts-select
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.ts-select').forEach((el) => {
                new TomSelect(el, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });

                // Auto-submit GET filter forms on change
                const form = el.form;
                if (form && form.getAttribute('method')?.toLowerCase() === 'get') {
                    el.addEventListener('change', function() {
                        form.submit();
                    });
                }
            });
        });

        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });

            // Close sidebar when clicking outside on mobile/tablet (<=992px)
            document.addEventListener('click', function(event) {
                if (window.innerWidth <= 992) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            // Close sidebar on link click (mobile/tablet)
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('show');
                    }
                });
            });
        }
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Apply unselectable class to body for global anti-cheat
            document.body.classList.add('anti-cheat-active');

            // Block Right Click
            document.addEventListener('contextmenu', event => event.preventDefault());

            // Block Copy, Cut, Paste
            ['copy', 'cut', 'paste'].forEach(function(eventType) {
                document.addEventListener(eventType, function(e) {
                    if (e.target && (
                        e.target.tagName === 'INPUT' || 
                        e.target.tagName === 'TEXTAREA' || 
                        e.target.closest('.allow-copy') || 
                        window.copyAllowed === true
                    )) {
                        return;
                    }
                    e.preventDefault();
                });
            });

            // Block PrintScreen and common dev tools shortcuts
            document.addEventListener('keyup', function(e) {
                if (e.key === 'PrintScreen' || e.keyCode === 44) {
                    navigator.clipboard.writeText(''); // Clear clipboard
                    alert('Screenshot dan Print Screen telah dinonaktifkan oleh sistem.');
                }
            });

            document.addEventListener('keydown', function(e) {
                // Block F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+S, Ctrl+P
                if (e.key === 'F12' || 
                   (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'i' || e.key === 'j')) || 
                   (e.ctrlKey && (e.key === 'U' || e.key === 'u' || e.key === 'S' || e.key === 's' || e.key === 'P' || e.key === 'p'))) {
                    e.preventDefault();
                }
            });
        });
    </script>

    <!-- Skeleton Page Transition Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show skeleton loading animation during page transitions
            window.addEventListener('beforeunload', function () {
                document.body.classList.add('page-loading');
            });

            // Handle page show (e.g. from back-forward cache) to hide loading state
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    document.body.classList.remove('page-loading');
                }
            });

            // Intercept form submissions
            document.querySelectorAll('form').forEach(form => {
                const target = form.getAttribute('target');
                const method = (form.getAttribute('method') || 'get').toLowerCase();
                // Exclude GET forms from page-loading overlay as they are fast and can be aborted by display:none
                if (target !== '_blank' && method !== 'get') {
                    form.addEventListener('submit', function () {
                        // Small delay to allow HTML5 validation check to run first
                        setTimeout(() => {
                            if (form.checkValidity()) {
                                document.body.classList.add('page-loading');
                            }
                        }, 50);
                    });
                }
            });

            // Intercept links to show loading immediately
            document.querySelectorAll('a').forEach(link => {
                const href = link.getAttribute('href');
                const target = link.getAttribute('target');
                if (href && 
                    !href.startsWith('#') && 
                    !href.startsWith('javascript:') && 
                    !href.startsWith('mailto:') && 
                    !href.startsWith('tel:') && 
                    target !== '_blank' && 
                    !link.hasAttribute('download') &&
                    !link.classList.contains('no-loader')) {
                    
                    link.addEventListener('click', function (e) {
                        // Only intercept left clicks without modifier keys
                        if (e.button === 0 && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {
                            document.body.classList.add('page-loading');
                        }
                    });
                }
            });
        });
    </script>

    <!-- Custom Confirmation Modal (ponytail: global alert replacement) -->
    <div class="modal fade" id="customConfirmModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden; background: #ffffff;">
                <div class="modal-body p-4 text-center">
                    <div class="confirm-icon-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; border-radius: 50%; transition: all 0.2s ease;">
                        <i style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.15rem;">Konfirmasi Tindakan</h5>
                    <p class="text-muted small mb-4 px-2" id="customConfirmMessage" style="font-family: 'Inter', sans-serif; line-height: 1.5; font-size: 0.88rem;"></p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-3 py-2 border w-50" style="border-radius: 10px; font-weight: 600; font-size: 0.85rem;" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn px-3 py-2 w-50 text-white" id="customConfirmYesBtn" style="border-radius: 10px; font-weight: 600; font-size: 0.85rem; border: none;"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal (ponytail: global alert dialog replacement) -->
    <div class="modal fade" id="customAlertModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden; background: #ffffff;">
                <div class="modal-body p-4 text-center">
                    <div class="alert-icon-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; border-radius: 50%; transition: all 0.2s ease;">
                        <i style="font-size: 1.5rem;"></i>
                    </div>
                    <h5 class="fw-bold mb-2 text-dark" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.15rem;">Pemberitahuan</h5>
                    <p class="text-muted small mb-4 px-2" id="customAlertMessage" style="font-family: 'Inter', sans-serif; line-height: 1.5; font-size: 0.88rem;"></p>
                    <button type="button" class="btn px-3 py-2 w-100 text-white" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; font-size: 0.85rem; border: none; background-color: var(--primary);">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ponytail: global alert override
        const nativeAlert = window.alert;
        window.alert = function(message) {
            if (window.showCustomAlert) {
                window.showCustomAlert(message);
            } else {
                nativeAlert(message);
            }
        };

        // ponytail: global non-blocking confirm modal implementation
        document.addEventListener('DOMContentLoaded', function() {
            // ponytail: alert modal setup
            const alertModalEl = document.getElementById('customAlertModal');
            if (alertModalEl) {
                const alertModal = new bootstrap.Modal(alertModalEl);
                const alertMessageEl = document.getElementById('customAlertMessage');
                const alertIconWrapper = alertModalEl.querySelector('.alert-icon-wrapper');
                const alertIconElement = alertIconWrapper.querySelector('i');

                window.showCustomAlert = function(message) {
                    alertMessageEl.textContent = message;

                    const lowerMsg = message.toLowerCase();
                    if (lowerMsg.includes('sukses') || lowerMsg.includes('berhasil') || lowerMsg.includes('salin')) {
                        alertIconWrapper.style.backgroundColor = 'rgba(25, 135, 84, 0.08)';
                        alertIconWrapper.style.color = '#198754';
                        alertIconElement.className = 'fas fa-check-circle';
                    } else if (lowerMsg.includes('nonaktif') || lowerMsg.includes('blokir') || lowerMsg.includes('sistem')) {
                        alertIconWrapper.style.backgroundColor = 'rgba(220, 53, 69, 0.08)';
                        alertIconWrapper.style.color = '#dc3545';
                        alertIconElement.className = 'fas fa-shield-alt';
                    } else {
                        alertIconWrapper.style.backgroundColor = 'rgba(255, 193, 7, 0.08)';
                        alertIconWrapper.style.color = '#ffc107';
                        alertIconElement.className = 'fas fa-exclamation-circle';
                    }

                    alertModal.show();
                };
            }

            const confirmModalEl = document.getElementById('customConfirmModal');
            if (!confirmModalEl) return;

            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const iconWrapper = confirmModalEl.querySelector('.confirm-icon-wrapper');
            const iconElement = iconWrapper.querySelector('i');
            const yesBtn = document.getElementById('customConfirmYesBtn');
            const messageEl = document.getElementById('customConfirmMessage');

            let onConfirmCallback = null;
            window.isCustomConfirming = false;

            // Override native confirm globally
            const nativeConfirm = window.confirm;
            window.confirm = function(message) {
                if (window.isCustomConfirming) {
                    return true;
                }
                return nativeConfirm(message);
            };

            window.showCustomConfirm = function(message, callback) {
                onConfirmCallback = callback;
                messageEl.textContent = message;

                // Adjust style dynamically based on message content
                const lowerMsg = message.toLowerCase();
                if (lowerMsg.includes('hapus') || lowerMsg.includes('delete') || lowerMsg.includes('destroy') || lowerMsg.includes('keluar') || lowerMsg.includes('batal')) {
                    // Danger/Delete style
                    iconWrapper.style.backgroundColor = 'rgba(220, 53, 69, 0.08)';
                    iconWrapper.style.color = '#dc3545';
                    iconElement.className = 'fas fa-exclamation-triangle';
                    yesBtn.className = 'btn btn-danger px-3 py-2 w-50';
                    yesBtn.style.backgroundColor = '#dc3545';
                    yesBtn.textContent = 'Ya, Hapus';
                } else if (lowerMsg.includes('kirim') || lowerMsg.includes('simpan') || lowerMsg.includes('isi') || lowerMsg.includes('regenerate')) {
                    // Success/Action style
                    iconWrapper.style.backgroundColor = 'rgba(25, 135, 84, 0.08)';
                    iconWrapper.style.color = '#198754';
                    iconElement.className = 'fas fa-check-circle';
                    yesBtn.className = 'btn btn-success px-3 py-2 w-50';
                    yesBtn.style.backgroundColor = '#198754';
                    yesBtn.textContent = 'Ya, Lanjutkan';
                } else {
                    // Default/Warning style
                    iconWrapper.style.backgroundColor = 'rgba(255, 193, 7, 0.08)';
                    iconWrapper.style.color = '#ffc107';
                    iconElement.className = 'fas fa-question-circle';
                    yesBtn.className = 'btn btn-warning text-dark px-3 py-2 w-50';
                    yesBtn.style.backgroundColor = '#ffc107';
                    yesBtn.textContent = 'Ya, Lanjutkan';
                }

                confirmModal.show();
            };

            yesBtn.addEventListener('click', function() {
                confirmModal.hide();
                if (onConfirmCallback) {
                    onConfirmCallback();
                }
            });

            // Intercept form submissions with confirm() in capturing phase
            document.addEventListener('submit', function(e) {
                const form = e.target;
                const onsubmitAttr = form.getAttribute('onsubmit');

                if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                    if (form.dataset.confirmed === 'true') {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();

                    const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                    const message = match ? match[1] : 'Apakah Anda yakin?';

                    window.showCustomConfirm(message, function() {
                        form.dataset.confirmed = 'true';
                        setTimeout(() => {
                            if (form.checkValidity()) {
                                document.body.classList.add('page-loading');
                            }
                        }, 50);
                        form.submit();
                    });
                }
            }, true);

            // Intercept inline onclick confirm calls
            document.addEventListener('click', function(e) {
                const button = e.target.closest('[onclick*="confirm("]');
                if (!button) return;

                if (button.dataset.confirmed === 'true') {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                const onclickAttr = button.getAttribute('onclick');
                const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
                const message = match ? match[1] : 'Apakah Anda yakin?';

                window.showCustomConfirm(message, function() {
                    button.dataset.confirmed = 'true';
                    window.isCustomConfirming = true;
                    button.click();
                    setTimeout(() => {
                        button.dataset.confirmed = 'false';
                        window.isCustomConfirming = false;
                    }, 500);
                });
            }, true);
        });
    </script>

    <!-- Visual Math Editor Modal -->
    <div class="modal fade" id="mathEditorModal" tabindex="-1" aria-labelledby="mathEditorModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: var(--radius-md); border: none; box-shadow: var(--shadow-lg);">
                <div class="modal-header bg-white border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="mathEditorModalLabel" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                        <i class="fas fa-calculator text-primary"></i> Pembuat Rumus Matematika (Visual)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-3">Tulis rumus secara visual dengan memilih simbol dari keyboard matematika di bawah. Klik tombol untuk mengetik variabel atau angka.</p>
                    
                    <!-- Mathfield element from MathLive -->
                    <div class="border rounded p-3 bg-light mb-3">
                        <math-field id="global-math-field" style="width: 100%; font-size: 1.5rem; background: #fff; padding: 12px; border: 1px solid #dee2e6; border-radius: 6px; min-height: 80px;"></math-field>
                    </div>

                    <div class="alert alert-light small py-2 px-3 mb-0" style="background: rgba(13,110,253,0.04); color: var(--primary); border: 1px solid rgba(13,110,253,0.1);">
                        <i class="fas fa-info-circle me-1"></i> Gunakan mouse/sentuh tombol visual, atau ketik langsung di keyboard Anda. Navigasi cursor dengan tombol panah.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: var(--radius-sm);">Batal</button>
                    <button type="button" class="btn btn-primary px-4" onclick="window.insertVisualMath()" style="border-radius: var(--radius-sm); background-color: var(--primary); border: none;">
                        <i class="fas fa-plus me-1"></i> Sisipkan ke Soal
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')

    <!-- KaTeX JS for rendering math formulas -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js" onload="window.renderMath()"></script>
    <!-- MathLive JS for visual math editor -->
    <script src="https://cdn.jsdelivr.net/npm/mathlive@0.98.6/dist/mathlive.min.js"></script>
    <script>
        window.renderMath = function() {
            if (typeof renderMathInElement === 'function') {
                renderMathInElement(document.body, {
                    delimiters: [
                        {left: "$$", right: "$$", display: true},
                        {left: "$", right: "$", display: false},
                        {left: "\\(", right: "\\)", display: false},
                        {left: "\\[", right: "\\]", display: true}
                    ],
                    throwOnError : false
                });
            }
        };
        window.insertMathCode = function(buttonEl, code, cursorOffset) {
            const toolbar = buttonEl.closest('.math-toolbar');
            if (!toolbar) return;
            const targetId = toolbar.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            input.focus();
            var startPos = input.selectionStart;
            var endPos = input.selectionEnd;
            
            // Check if cursor is already inside math delimiters \( ... \)
            var textBefore = input.value.substring(0, startPos);
            var openCount = (textBefore.match(/\\\(/g) || []).length;
            var closeCount = (textBefore.match(/\\\)/g) || []).length;
            var inMath = openCount > closeCount;

            var insertValue = code;
            var finalOffset = cursorOffset;

            if (!inMath && code.indexOf('\\(') === -1) {
                insertValue = '\\( ' + code + ' \\)';
                if (finalOffset !== undefined) {
                    finalOffset = finalOffset + 3; // Account for '\( ' prefix
                }
            }

            input.value = input.value.substring(0, startPos) + insertValue + input.value.substring(endPos, input.value.length);
            
            var newCursorPos = startPos + (finalOffset !== undefined ? finalOffset : insertValue.length);
            input.selectionStart = newCursorPos;
            input.selectionEnd = newCursorPos;

            // Trigger input event to update preview
            input.dispatchEvent(new Event('input'));
            input.dispatchEvent(new Event('change'));
        };
        let activeMathTargetInput = null;
        window.openVisualMathEditor = function(buttonEl) {
            const toolbar = buttonEl.closest('.math-toolbar');
            if (!toolbar) return;
            const targetId = toolbar.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            activeMathTargetInput = input;
            
            const mf = document.getElementById('global-math-field');
            if (mf) {
                mf.value = '';
            }
            
            const modalEl = document.getElementById('mathEditorModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            
            setTimeout(() => {
                mf.focus();
            }, 450);
        };

        window.insertVisualMath = function() {
            if (!activeMathTargetInput) return;
            const mf = document.getElementById('global-math-field');
            if (!mf) return;

            const latex = mf.value;
            if (latex.trim()) {
                const input = activeMathTargetInput;
                input.focus();
                var startPos = input.selectionStart;
                var endPos = input.selectionEnd;
                
                const insertValue = '\\( ' + latex + ' \\)';
                
                input.value = input.value.substring(0, startPos) + insertValue + input.value.substring(endPos, input.value.length);
                
                var newCursorPos = startPos + insertValue.length;
                input.selectionStart = newCursorPos;
                input.selectionEnd = newCursorPos;

                input.dispatchEvent(new Event('input'));
                input.dispatchEvent(new Event('change'));
            }
            
            const modalEl = document.getElementById('mathEditorModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        };
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(window.renderMath, 100);
        });
    </script>

    @auth
    <script>
        // PWA and Web Push Notification Setup
        const VAPID_PUBLIC_KEY = "{{ env('VAPID_PUBLIC_KEY') }}";

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        if ('serviceWorker' in navigator && 'PushManager' in window) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker registered with scope:', registration.scope);
                        
                        if (Notification.permission === 'granted') {
                            subscribeUserToPush(registration);
                        } else if (Notification.permission === 'default') {
                            setTimeout(() => {
                                window.showCustomConfirm('Aktifkan notifikasi browser agar Anda mendapatkan info tugas & nilai terbaru secara langsung?', function() {
                                    Notification.requestPermission().then(function(permission) {
                                        if (permission === 'granted') {
                                            subscribeUserToPush(registration);
                                        }
                                    });
                                });
                            }, 5000);
                        }
                    })
                    .catch(function(error) {
                        console.error('Service Worker registration failed:', error);
                    });
            });
        }

        function subscribeUserToPush(registration) {
            if (!VAPID_PUBLIC_KEY) {
                console.warn('VAPID Public Key missing. Cannot subscribe to Web Push.');
                return;
            }

            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
            };

            registration.pushManager.subscribe(subscribeOptions)
                .then(function(pushSubscription) {
                    fetch("{{ route('push.subscribe') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify(pushSubscription)
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Subscribed to Web Push successfully.');
                    })
                    .catch(err => {
                        console.error('Failed to send push subscription to server:', err);
                    });
                })
                .catch(function(error) {
                    console.error('Failed to subscribe user to push:', error);
                });
        }

        function markAllNotificationsRead(e) {
            if (e) e.preventDefault();
            fetch("{{ route('notifications.read-all') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                const headerBadge = document.getElementById('notificationHeaderBadge');
                const btnMarkRead = document.getElementById('btnMarkAllRead');
                if (badge) badge.classList.add('d-none');
                if (headerBadge) headerBadge.classList.add('d-none');
                if (btnMarkRead) btnMarkRead.classList.add('d-none');

                document.querySelectorAll('#notificationListBody .dropdown-item').forEach(el => {
                    el.style.backgroundColor = 'transparent';
                    const circle = el.querySelector('.rounded-circle');
                    if (circle) circle.style.backgroundColor = '#6c757d';
                    const dot = el.querySelector('.unread-dot');
                    if (dot) dot.remove();
                });
            })
            .catch(err => console.error('Error marking all as read:', err));
        }

        function handleNotificationClick(e, readUrl, targetUrl, notificationId) {
            e.preventDefault();
            fetch(readUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                const dest = (data && data.url) ? data.url : targetUrl;
                if (dest && dest !== '') {
                    window.location.href = dest;
                } else {
                    const item = document.querySelector('.notification-item-' + notificationId);
                    if (item) {
                        item.style.backgroundColor = 'transparent';
                        const circle = item.querySelector('.rounded-circle');
                        if (circle) circle.style.backgroundColor = '#6c757d';
                        const dot = item.querySelector('.unread-dot');
                        if (dot) dot.remove();
                    }
                    pollNotifications();
                }
            })
            .catch(err => {
                if (targetUrl && targetUrl !== '') {
                    window.location.href = targetUrl;
                }
            });
        }

        // Live notification polling for popup count & status updates
        function pollNotifications() {
            fetch(`/notifications/poll`)
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    const headerBadge = document.getElementById('notificationHeaderBadge');
                    const countText = document.getElementById('notificationUnreadCountText');
                    const btnMarkRead = document.getElementById('btnMarkAllRead');
                    
                    if (data.unread_count > 0) {
                        if (badge) {
                            badge.textContent = data.unread_count;
                            badge.classList.remove('d-none');
                        }
                        if (headerBadge) headerBadge.classList.remove('d-none');
                        if (countText) countText.textContent = data.unread_count;
                        if (btnMarkRead) btnMarkRead.classList.remove('d-none');
                    } else {
                        if (badge) badge.classList.add('d-none');
                        if (headerBadge) headerBadge.classList.add('d-none');
                        if (btnMarkRead) btnMarkRead.classList.add('d-none');
                    }
                })
                .catch(err => console.error('Error polling notifications:', err));
        }

        setInterval(pollNotifications, 30000);
    </script>
    @endauth

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.className = 'far fa-eye-slash';
            } else {
                input.type = 'password';
                if (icon) icon.className = 'far fa-eye';
            }
        }
    </script>

    <!-- Chart.js for LMS Curves & Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @stack('scripts')
</body>
</html>

