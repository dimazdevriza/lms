@extends('layouts.lms')

@section('title', 'Pertemuan - ' . $subject->name)

@section('content')
<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('admin.attendances.index') }}" class="text-decoration-none">Presensi</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.attendances.showClass', $class->id) }}" class="text-decoration-none">{{ $class->name }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $subject->name }}</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.5rem;">
            📅 Daftar Pertemuan — {{ $subject->name }}
        </h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Kelas: <strong>{{ $class->name }}</strong> ({{ $class->students_count }} Siswa)</p>
    </div>
    <a href="{{ route('admin.attendances.showClass', $class->id) }}" class="btn btn-sm btn-outline-secondary rounded-3">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Mapel
    </a>
</div>

@if($meetings->isEmpty())
    <div class="content-card text-center p-5">
        <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
        <h5 class="text-secondary fw-semibold">Belum Ada Pertemuan</h5>
        <p class="text-muted small mb-0">Belum ada pertemuan yang dibuat oleh guru untuk mata pelajaran {{ $subject->name }} di kelas {{ $class->name }}.</p>
    </div>
@else
    <div class="content-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3" style="width: 130px;">Pertemuan</th>
                        <th class="py-3" style="width: 140px;">Tanggal</th>
                        <th class="py-3">Guru Pengampu</th>
                        <th class="py-3">Topik / Judul Pertemuan</th>
                        <th class="py-3">Status Presensi</th>
                        <th class="text-end pe-4 py-3" style="width: 220px; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($meetings as $meeting)
                        @php
                            $att = $meeting->attendance;
                            $details = $att ? $att->details : collect();
                            $totalFilled = $details->count();
                            $hadirCount = $details->where('status', 'hadir')->count();
                            $izinCount = $details->where('status', 'izin')->count();
                            $sakitCount = $details->where('status', 'sakit')->count();
                            $alpaCount = $details->where('status', 'alpa')->count();
                            $cabutCount = $details->where('status', 'cabut')->count();
                            $firstMaterial = $meeting->materials->first();
                            $firstAssignment = $meeting->assignments->first();
                        @endphp
                        <tr>
                            <td class="ps-4 fw-bold text-primary">
                                Pertemuan #{{ $meeting->number }}
                            </td>
                            <td>
                                <i class="far fa-calendar text-muted me-1"></i>
                                {{ \Carbon\Carbon::parse($meeting->date)->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $meeting->teacher->user->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $meeting->title ?? '-' }}</div>
                                @if($meeting->description)
                                    <small class="text-muted text-truncate d-block" style="max-width: 250px;">{{ $meeting->description }}</small>
                                @endif
                            </td>
                            <td>
                                @if($att && $totalFilled > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-medium">
                                        <i class="fas fa-check-circle me-1"></i>{{ $totalFilled }}/{{ $class->students_count }} Siswa Terisi
                                    </span>
                                    <div class="small text-muted mt-1">
                                        <span class="text-success fw-semibold">{{ $hadirCount }} H</span> ·
                                        <span class="text-warning fw-semibold">{{ $izinCount }} I</span> ·
                                        <span class="text-info fw-semibold">{{ $sakitCount }} S</span> ·
                                        <span class="text-danger fw-semibold">{{ $alpaCount }} A</span>
                                        @if($cabutCount > 0)
                                            · <span class="text-secondary fw-semibold">{{ $cabutCount }} C</span>
                                        @endif
                                    </div>
                                    @if($att->formatted_submitted_time)
                                        <div class="small text-muted mt-1">
                                            <i class="far fa-clock text-primary me-1"></i>Diisi jam <strong>{{ $att->formatted_submitted_time }} WIB</strong>
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-medium">
                                        <i class="fas fa-exclamation-circle me-1"></i>Belum Diisi Guru
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4" style="white-space: nowrap;">
                                <div class="d-inline-flex gap-1 justify-content-end align-items-center flex-nowrap">
                                    <a href="{{ route('admin.attendances.meetingMaterials', $meeting->id) }}" class="btn btn-outline-info rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;" title="Kelola Materi Pertemuan #{{ $meeting->number }}">
                                        <i class="fas fa-book me-1" style="font-size: 0.7rem;"></i> Materi @if($meeting->materials->count() > 0)<span class="badge bg-info text-white ms-1 rounded-pill" style="font-size: 0.65rem; padding: 2px 5px;">{{ $meeting->materials->count() }}</span>@endif
                                    </a>

                                    <a href="{{ route('admin.attendances.meetingAssignments', $meeting->id) }}" class="btn btn-outline-warning text-dark rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;" title="Kelola Tugas Pertemuan #{{ $meeting->number }}">
                                        <i class="fas fa-file-alt me-1" style="font-size: 0.7rem;"></i> Tugas @if($meeting->assignments->count() > 0)<span class="badge bg-warning text-dark ms-1 rounded-pill" style="font-size: 0.65rem; padding: 2px 5px;">{{ $meeting->assignments->count() }}</span>@endif
                                    </a>

                                    <a href="{{ route('admin.attendances.editMeeting', $meeting->id) }}" class="btn {{ $att ? 'btn-outline-success' : 'btn-primary' }} rounded-2 fw-semibold" style="font-size: 0.725rem; padding: 3px 8px;" title="{{ $att ? 'Edit Presensi' : 'Isi Presensi' }}">
                                        <i class="fas {{ $att ? 'fa-user-check' : 'fa-pen' }} me-1" style="font-size: 0.7rem;"></i> {{ $att ? 'Edit' : 'Presensi' }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
