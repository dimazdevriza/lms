@extends('layouts.lms')

@section('title', 'Jadwal Kelas ' . $schoolClass->name)

@section('content')
<div class="schedule-editor-page">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3 reveal">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('tatausaha.schedules.index', ['academic_year_id' => $selectedYearId]) }}" class="btn btn-outline-secondary-theme btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <div>
                <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--primary); margin-bottom: 4px;">
                    Jadwal Kelas {{ $schoolClass->name }}
                </h1>
                <p class="text-muted mb-0 small">
                    @if($schoolClass->major)
                        <span class="status-badge status-badge--hadir" style="font-size: 0.7rem;">{{ $schoolClass->major }}</span>
                    @endif
                    Susun jadwal mingguan untuk kelas ini
                </p>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearScheduleModal">
                <i class="fas fa-eraser me-1"></i> Kosongkan Semua
            </button>
            <a href="{{ route('tatausaha.schedules.print', ['academic_year_id' => $selectedYearId, 'class_id' => $schoolClass->id]) }}" 
               target="_blank" class="btn btn-sm btn-outline-accent-theme">
                <i class="fas fa-print me-1"></i> Cetak Jadwal
            </a>
        </div>
    </div>

    {{-- Grid Editor --}}
    <form method="POST" action="{{ route('tatausaha.schedules.update', $schoolClass) }}" id="scheduleForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

        <div class="content-card reveal reveal-delay-1" style="cursor: default;">
            <div class="content-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 schedule-grid">
                        <thead>
                            <tr>
                                <th class="slot-header" style="width: 140px; min-width: 140px;">
                                    <div class="text-center">
                                        <i class="fas fa-clock me-1"></i> Waktu
                                    </div>
                                </th>
                                @foreach($days as $day)
                                    <th class="day-header text-center">{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php $slotIndex = 0; @endphp
                            @foreach($timeSlots as $slot)
                                <tr class="{{ $slot->isBreak() ? 'break-row' : 'lesson-row' }}">
                                    {{-- Kolom Waktu --}}
                                    <td class="slot-label-cell" 
                                        style="cursor: pointer;" 
                                        title="Klik untuk edit jam pelajaran ini"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTimeSlotModal"
                                        data-slot-order="{{ $slot->slot_order }}"
                                        data-label="{{ $slot->label }}"
                                        data-type="{{ $slot->type }}"
                                        data-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                        data-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}">
                                        <div class="slot-label {{ $slot->isBreak() ? 'break-label' : '' }}">
                                            <div class="fw-bold text-dark" style="font-size: 0.88rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                                                {{ $slot->label }}
                                            </div>
                                            <div class="fw-bold" style="font-size: 0.78rem; color: #0f172a; margin-top: 2px;">
                                                <i class="far fa-clock me-1 text-success" style="font-size: 0.72rem;"></i>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge" style="background: rgba(67, 160, 71, 0.12); color: #1e7e34; font-size: 0.65rem; font-weight: 700; border: 1px solid rgba(67, 160, 71, 0.3);">
                                                    <i class="fas fa-pen me-1"></i> Edit Waktu
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach($days as $day)
                                        <td class="{{ $slot->isBreak() ? 'break-cell' : 'lesson-cell' }}">
                                            @if($slot->isBreak())
                                                <div class="break-indicator">
                                                    <i class="fas fa-coffee"></i> {{ $slot->label }}
                                                </div>
                                            @else
                                                @php
                                                    $key = $day . '_' . $slot->id;
                                                    $existing = $schedules[$key] ?? null;
                                                    $fieldName = "slots[{$slotIndex}_{$loop->index}]";
                                                @endphp
                                                <input type="hidden" name="{{ $fieldName }}[day]" value="{{ $day }}">
                                                <input type="hidden" name="{{ $fieldName }}[time_slot_id]" value="{{ $slot->id }}">
                                                
                                                <div class="schedule-cell-card">
                                                    <select name="{{ $fieldName }}[subject_id]" 
                                                            class="form-select form-select-sm subject-select"
                                                            data-day="{{ $day }}" 
                                                            data-slot="{{ $slot->id }}"
                                                            onchange="onSubjectChange(this)">
                                                        <option value="">— Kosong —</option>
                                                        <option value="__upacara__" @selected($existing && $existing->activity === 'Upacara')>🎌 Upacara</option>
                                                        @foreach($uniqueSubjects as $subj)
                                                            <option value="{{ $subj->id }}" 
                                                                    data-teacher-id="{{ $classAssignments[$subj->id]->teacher_id ?? $subj->teacher_id ?? '' }}"
                                                                    @selected($existing && $existing->subject && $existing->subject->name == $subj->name)>
                                                                {{ $subj->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    
                                                    <select name="{{ $fieldName }}[teacher_id]" 
                                                            class="form-select form-select-sm teacher-select"
                                                            id="teacher_{{ $day }}_{{ $slot->id }}">
                                                        <option value="">— Guru —</option>
                                                        @foreach($teachers as $t)
                                                            <option value="{{ $t->id }}" @selected($existing && $existing->teacher_id == $t->id)>
                                                                {{ $t->user->name ?? '-' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @if(!$slot->isBreak())
                                    @php $slotIndex++; @endphp
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="d-flex justify-content-end align-items-center mt-4 reveal reveal-delay-2">
            <button type="submit" class="btn btn-lg btn-outline-primary-theme" style="padding: 12px 40px;">
                <i class="fas fa-save me-2"></i> Simpan Jadwal
            </button>
        </div>
    </form>
</div>

@push('modals')
    <div class="modal fade" id="clearScheduleModal" tabindex="-1" aria-labelledby="clearScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 14px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="clearScheduleModalLabel" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary);">
                        <i class="fas fa-triangle-exclamation text-danger me-2"></i> Kosongkan Semua Jadwal?
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                        Seluruh jadwal untuk <strong>Kelas {{ $schoolClass->name }}</strong> akan dihapus permanen dari database dan tidak dapat dikembalikan. Lanjutkan?
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary-theme btn-sm" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" action="{{ route('tatausaha.schedules.clear', $schoolClass) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-eraser me-1"></i> Ya, Kosongkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTimeSlotModal" aria-labelledby="editTimeSlotModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 14px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="editTimeSlotModalLabel" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: var(--primary);">
                        <i class="fas fa-clock text-success me-2"></i> Edit Jam Pelajaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('tatausaha.schedules.time-slots.store') }}">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Urutan Slot</label>
                            <input type="number" name="slot_order" id="modal_slot_order" class="form-control form-control-sm" min="1" required placeholder="Nomor urut slot (misal: 1)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Tipe Slot</label>
                            <select name="type" id="modal_type" class="form-select form-select-sm" required>
                                <option value="lesson">📖 Pelajaran</option>
                                <option value="break">☕ Istirahat</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Label Jam / Waktu</label>
                            <input type="text" name="label" id="modal_label" class="form-control form-control-sm" required placeholder="Misal: Jam ke-1">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Jam Mulai</label>
                                <input type="time" name="start_time" id="modal_start_time" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Jam Selesai</label>
                                <input type="time" name="end_time" id="modal_end_time" class="form-control form-control-sm" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary-theme btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-outline-primary-theme btn-sm">
                            <i class="fas fa-save me-1"></i> Simpan Jam Pelajaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('styles')
<style>
    .schedule-editor-page .content-card:hover {
        transform: none !important;
    }

    .schedule-grid {
        border-collapse: collapse;
    }

    .schedule-grid th,
    .schedule-grid td {
        border: 1px solid #f1f3f5 !important;
        vertical-align: middle;
    }

    .slot-header {
        background: #ffffff !important;
        color: var(--primary) !important;
        border-bottom: 2px solid var(--secondary) !important;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 14px 12px !important;
        position: sticky;
        left: 0;
        z-index: 2;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.02);
    }

    .day-header {
        background: #ffffff !important;
        color: var(--primary) !important;
        border-bottom: 2px solid var(--secondary) !important;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 14px 8px !important;
        min-width: 170px;
        letter-spacing: 0.5px;
    }

    .slot-label-cell {
        background: #fafafa;
        padding: 10px 12px !important;
        position: sticky;
        left: 0;
        z-index: 1;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.02);
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .slot-label-cell:hover {
        background-color: #e8f5e9 !important;
    }

    .slot-label-cell:hover .slot-edit-icon {
        color: var(--primary) !important;
        opacity: 1 !important;
    }

    .slot-label {
        text-align: center;
    }

    .slot-label.break-label {
        color: var(--accent);
    }

    .lesson-cell {
        padding: 8px !important;
        background: #fdfdfd;
    }

    /* Card container inside cells */
    .schedule-cell-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        display: flex;
        flex-direction: column;
        gap: 3px;
        transition: all 0.2s ease;
        position: relative;
    }

    .schedule-cell-card:hover {
        border-color: rgba(67, 160, 71, 0.4);
        box-shadow: 0 4px 12px rgba(67, 160, 71, 0.08);
    }

    .schedule-cell-card.has-assignment {
        background: #f4faf3;
        border-color: rgba(67, 160, 71, 0.35);
    }

    .break-row {
        background: #fdfdfd;
    }

    .break-cell {
        padding: 8px !important;
        background: #fffdf5;
        border-top: 1px solid rgba(249, 168, 37, 0.15) !important;
        border-bottom: 1px solid rgba(249, 168, 37, 0.15) !important;
    }

    .break-indicator {
        text-align: center;
        color: #b8860b;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px;
    }

    /* Clean borderless select inputs */
    .subject-select,
    .teacher-select {
        border: none !important;
        background-color: transparent !important;
        box-shadow: none !important;
        outline: none !important;
        padding: 2px 1.1rem 2px 4px !important;
        background-position: right 0.2rem center !important;
        background-size: 9px 7px !important;
        border-radius: 4px;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    .subject-select {
        font-size: 0.82rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        height: 28px !important;
    }

    .teacher-select {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-top: 1px;
        height: 26px !important;
    }

    .subject-select option,
    .teacher-select option {
        background-color: #ffffff !important;
        color: #2d3748 !important;
        font-weight: normal !important;
    }
</style>
@endpush

<script>
    const subjectTeachersMap = @json($subjectTeachersMap);
    const allTeachers = @json($teachers->map(fn($t) => ['id' => $t->id, 'name' => $t->user->name ?? '-'])->values());

    function onSubjectChange(selectEl) {
        const day = selectEl.dataset.day;
        const slotId = selectEl.dataset.slot;
        const teacherSelect = document.getElementById('teacher_' + day + '_' + slotId);
        if (!teacherSelect) return;

        const subjectId = selectEl.value;

        // Kegiatan non-mapel (Upacara): tidak perlu guru
        if (subjectId === '__upacara__') {
            teacherSelect.innerHTML = '<option value="">—</option>';
            teacherSelect.value = '';
            teacherSelect.disabled = true;
            selectEl.classList.add('has-value');
            const upCard = selectEl.closest('.schedule-cell-card');
            if (upCard) upCard.classList.add('has-assignment');
            return;
        }
        teacherSelect.disabled = false;

        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const suggestedTeacherId = selectedOption.dataset.teacherId;

        const currentTeacherId = teacherSelect.value;

        teacherSelect.innerHTML = '<option value="">— Guru —</option>';

        if (subjectId && subjectTeachersMap[subjectId]) {
            const allowedIds = (subjectTeachersMap[subjectId] || []).map(Number);
            allTeachers.forEach(t => {
                if (allowedIds.includes(Number(t.id))) {
                    const opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name;
                    teacherSelect.appendChild(opt);
                }
            });

            if (suggestedTeacherId) {
                teacherSelect.value = suggestedTeacherId;
            } else if (allowedIds.length === 1) {
                teacherSelect.value = allowedIds[0];
            }
        } else if (subjectId) {
            allTeachers.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                teacherSelect.appendChild(opt);
            });
        }

        const card = selectEl.closest('.schedule-cell-card');
        if (subjectId) {
            selectEl.classList.add('has-value');
            if (card) card.classList.add('has-assignment');
        } else {
            selectEl.classList.remove('has-value');
            if (card) card.classList.remove('has-assignment');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.subject-select').forEach(sel => {
            if (sel.value) {
                sel.classList.add('has-value');
                const card = sel.closest('.schedule-cell-card');
                if (card) card.classList.add('has-assignment');

                const day = sel.dataset.day;
                const slotId = sel.dataset.slot;
                const teacherSelect = document.getElementById('teacher_' + day + '_' + slotId);
                if (!teacherSelect) return;

                const savedTeacherId = teacherSelect.value;
                const subjectId = sel.value;

                // Kegiatan non-mapel (Upacara): tidak perlu guru
                if (subjectId === '__upacara__') {
                    teacherSelect.innerHTML = '<option value="">—</option>';
                    teacherSelect.value = '';
                    teacherSelect.disabled = true;
                    return;
                }

                teacherSelect.innerHTML = '<option value="">— Guru —</option>';

                if (subjectTeachersMap[subjectId]) {
                    const allowedIds = (subjectTeachersMap[subjectId] || []).map(Number);
                    allTeachers.forEach(t => {
                        if (allowedIds.includes(Number(t.id))) {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.textContent = t.name;
                            if (t.id == savedTeacherId) opt.selected = true;
                            teacherSelect.appendChild(opt);
                        }
                    });
                } else {
                    allTeachers.forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t.id;
                        opt.textContent = t.name;
                        if (t.id == savedTeacherId) opt.selected = true;
                        teacherSelect.appendChild(opt);
                    });
                }
            }
        });
    });

    const editTimeSlotModal = document.getElementById('editTimeSlotModal');
    if (editTimeSlotModal) {
        editTimeSlotModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const cell = trigger ? (trigger.closest('.slot-label-cell') || trigger) : null;
            if (!cell) return;

            document.getElementById('modal_slot_order').value = cell.getAttribute('data-slot-order') || '';
            document.getElementById('modal_type').value = cell.getAttribute('data-type') || 'lesson';
            document.getElementById('modal_label').value = cell.getAttribute('data-label') || '';
            document.getElementById('modal_start_time').value = cell.getAttribute('data-start') || '';
            document.getElementById('modal_end_time').value = cell.getAttribute('data-end') || '';
        });
    }
</script>
@endsection
