@extends('layouts.lms')

@section('title', 'Kelola Jam Pelajaran')

@section('content')
<div class="timeslots-page">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div>
            <a href="{{ route('tatausaha.schedules.index', ['academic_year_id' => $selectedYearId]) }}" class="text-decoration-none small" style="color: var(--secondary);">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Jadwal
            </a>
            <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--primary); margin-bottom: 4px; margin-top: 8px;">
                Kelola Jam Pelajaran
            </h1>
            <p class="text-muted mb-0 small">Atur jam pelajaran dan waktu istirahat yang berlaku untuk seluruh kelas.</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('tatausaha.schedules.time-slots') }}" class="content-card mb-4 reveal reveal-delay-1" style="border-top: 3px solid var(--accent);">
        <div class="content-card-body" style="padding: 20px 28px;">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted"><i class="fas fa-calendar me-1"></i> Tahun Ajar</label>
                    <select name="academic_year_id" class="form-select form-select-sm ts-select">
                        <option value="">Pilih Tahun Ajar</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" @selected($selectedYearId == $ay->id)>
                                {{ $ay->name }} {{ $ay->is_active ? '✅ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-sm btn-outline-primary-theme">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if($selectedYearId)
        <div class="row g-4">
            {{-- Tabel Existing Slots --}}
            <div class="col-md-7">
                <div class="content-card reveal reveal-delay-2" style="cursor: default;">
                    <div class="py-3 px-4" style="background: linear-gradient(135deg, var(--primary), var(--secondary));">
                        <h6 class="mb-0 text-white fw-bold"><i class="fas fa-list me-2"></i> Daftar Jam Pelajaran</h6>
                    </div>
                    <div class="content-card-body" style="padding: 0;">
                        @if($timeSlots->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4" style="width: 50px;">URUTAN</th>
                                            <th>LABEL</th>
                                            <th>TIPE</th>
                                            <th>WAKTU</th>
                                            <th style="text-align: center; width: 80px;">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timeSlots as $slot)
                                            <tr class="{{ $slot->isBreak() ? 'break-row-highlight' : '' }}">
                                                <td class="ps-4 text-center fw-bold" style="color: var(--primary);">{{ $slot->slot_order }}</td>
                                                <td>
                                                    <span class="fw-semibold" style="color: {{ $slot->isBreak() ? '#b8860b' : 'var(--primary)' }};">
                                                        @if($slot->isBreak()) ☕ @else 📖 @endif
                                                        {{ $slot->label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($slot->isBreak())
                                                        <span class="status-badge" style="background: rgba(249, 168, 37, 0.12); color: #B26A00;">Istirahat</span>
                                                    @else
                                                        <span class="status-badge status-badge--hadir">Pelajaran</span>
                                                    @endif
                                                </td>
                                                <td class="small text-muted">
                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} — {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary me-1" 
                                                            title="Edit"
                                                            onclick="populateSlotEdit({{ $slot->slot_order }}, '{{ $slot->type }}', '{{ addslashes($slot->label) }}', '{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}', '{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}')">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('tatausaha.schedules.time-slots.destroy', $slot) }}" class="d-inline" onsubmit="return confirm('Hapus slot ini? Jadwal terkait juga akan terhapus.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="empty-state-text">Belum ada jam pelajaran. Tambahkan di form samping.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Form Tambah Slot --}}
            <div class="col-md-5">
                <div class="content-card reveal reveal-delay-2" style="cursor: default;">
                    <div class="py-3 px-4" style="background: linear-gradient(135deg, var(--accent), #f5c842);">
                        <h6 class="mb-0 fw-bold" style="color: #333;"><i class="fas fa-plus-circle me-2"></i> Tambah / Edit Slot</h6>
                    </div>
                    <div class="content-card-body">
                        <form method="POST" action="{{ route('tatausaha.schedules.time-slots.store') }}">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Urutan Slot</label>
                                <input type="number" name="slot_order" id="slotOrder" class="form-control form-control-sm" 
                                       value="{{ $timeSlots->count() + 1 }}" min="1" required
                                       placeholder="Nomor urut (misal: 1, 2, 3...)">
                                <div class="form-text">Jika urutan sudah ada, data akan diupdate.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tipe</label>
                                <select name="type" class="form-select form-select-sm" id="slotType" onchange="updateLabel()">
                                    <option value="lesson">📖 Pelajaran</option>
                                    <option value="break">☕ Istirahat</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Label</label>
                                <input type="text" name="label" class="form-control form-control-sm" id="slotLabel" 
                                       placeholder="Misal: Jam ke-1, Istirahat 1" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Mulai</label>
                                    <input type="time" name="start_time" id="startTime" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Selesai</label>
                                    <input type="time" name="end_time" id="endTime" class="form-control form-control-sm" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-sm w-100 btn-outline-primary-theme">
                                <i class="fas fa-save me-1"></i> Simpan Slot
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Info Card --}}
                <div class="content-card reveal reveal-delay-3" style="cursor: default;">
                    <div class="content-card-header">
                        <div class="content-card-header-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h2 class="content-card-title">Panduan</h2>
                    </div>
                    <div class="content-card-body">
                        <ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
                            <li class="mb-2">Jam pelajaran digunakan sebagai <strong>baris</strong> di jadwal mingguan</li>
                            <li class="mb-2">Tipe <strong>Istirahat</strong> akan ditampilkan sebagai baris kosong</li>
                            <li class="mb-2">Jika <strong>urutan</strong> sudah ada, data slot akan diperbarui</li>
                            <li>Menghapus slot akan menghapus jadwal terkait</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="content-card reveal reveal-delay-2">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="empty-state-text">Pilih tahun ajar terlebih dahulu.</div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .timeslots-page .content-card:hover {
        transform: none !important;
        cursor: default;
    }

    .break-row-highlight {
        background-color: rgba(249, 168, 37, 0.06);
    }

    .break-row-highlight td {
        border-left: 3px solid var(--accent) !important;
    }
</style>
@endpush

<script>
    function updateLabel() {
        const type = document.getElementById('slotType').value;
        const label = document.getElementById('slotLabel');
        if (type === 'break' && !label.value) {
            label.placeholder = 'Misal: Istirahat 1';
        } else if (!label.value) {
            label.placeholder = 'Misal: Jam ke-1';
        }
    }

    function populateSlotEdit(order, type, label, start, end) {
        document.getElementById('slotOrder').value = order;
        document.getElementById('slotType').value = type;
        document.getElementById('slotLabel').value = label;
        document.getElementById('startTime').value = start;
        document.getElementById('endTime').value = end;
        document.getElementById('slotLabel').focus();
    }
</script>
@endsection
