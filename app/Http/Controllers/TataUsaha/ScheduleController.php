<?php

namespace App\Http\Controllers\TataUsaha;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubjectTeacher;
use App\Models\Meeting;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Halaman utama jadwal — daftar kelas untuk dipilih
     */
    public function index(Request $request): View
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeYear?->id ?? null);

        $classes = SchoolClass::orderBy('name')->get();

        // Hitung jumlah slot terisi per kelas
        $filledCounts = [];
        if ($selectedYearId) {
            $counts = Schedule::where('academic_year_id', $selectedYearId)
                ->where(function ($q) {
                    $q->whereNotNull('subject_id')->orWhereNotNull('activity');
                })
                ->selectRaw('class_id, count(*) as total')
                ->groupBy('class_id')
                ->pluck('total', 'class_id');
            $filledCounts = $counts->toArray();
        }

        $timeSlotCount = $selectedYearId
            ? TimeSlot::where('academic_year_id', $selectedYearId)->where('type', 'lesson')->count()
            : 0;

        return view('tatausaha.schedules.index', compact(
            'classes', 'academicYears', 'selectedYearId', 'filledCounts', 'timeSlotCount'
        ));
    }

    /**
     * Editor jadwal untuk satu kelas — grid interaktif
     */
    public function edit(Request $request, SchoolClass $schoolClass): View
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeYear?->id ?? null);

        if (!$selectedYearId) {
            return redirect()->route('tatausaha.schedules.index')
                ->withErrors(['academic_year_id' => 'Pilih tahun ajar terlebih dahulu.']);
        }

        // Ambil time slots, buat default jika belum ada
        $timeSlots = TimeSlot::where('academic_year_id', $selectedYearId)
            ->orderBy('slot_order')
            ->get();

        if ($timeSlots->isEmpty()) {
            $this->seedDefaultTimeSlots($selectedYearId);
            $timeSlots = TimeSlot::where('academic_year_id', $selectedYearId)
                ->orderBy('slot_order')
                ->get();
        }

        // Ambil jadwal yang sudah ada
        $schedules = Schedule::where('academic_year_id', $selectedYearId)
            ->where('class_id', $schoolClass->id)
            ->get()
            ->keyBy(fn($s) => $s->day . '_' . $s->time_slot_id);

        // Ambil semua mapel + guru untuk dropdown
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->orderBy('id')->get();

        // Ambil penugasan guru untuk kelas ini (untuk auto-suggest guru)
        $classAssignments = ClassSubjectTeacher::where('class_id', $schoolClass->id)
            ->where('academic_year_id', $selectedYearId)
            ->get()
            ->keyBy('subject_id');

        // Untuk dropdown mapel, kita buat unik berdasarkan nama agar tidak muncul dobel
        $uniqueSubjects = $subjects->unique('name');

        // Bangun mapping subject_id -> [teacher_ids] untuk filter dropdown guru
        // Sumber: gabungkan dari semua subject dengan nama yang sama
        $subjectTeachersMap = [];
        foreach ($uniqueSubjects as $subj) {
            $name = $subj->name;
            $sharedIds = $subjects->where('name', $name)->pluck('id')->toArray();
            
            $teacherIds = [];
            // Dari penugasan kelas ini
            foreach ($sharedIds as $sid) {
                if (isset($classAssignments[$sid]) && !in_array($classAssignments[$sid]->teacher_id, $teacherIds)) {
                    $teacherIds[] = $classAssignments[$sid]->teacher_id;
                }
            }

            // Dari default teacher di subject
            foreach ($subjects->where('name', $name) as $s) {
                if ($s->teacher_id && !in_array($s->teacher_id, $teacherIds)) {
                    $teacherIds[] = $s->teacher_id;
                }
            }

            // Cari juga di seluruh penugasan tahun ini untuk group ini
            $allTeacherIds = ClassSubjectTeacher::whereIn('subject_id', $sharedIds)
                ->where('academic_year_id', $selectedYearId)
                ->pluck('teacher_id')
                ->toArray();
                
            foreach ($allTeacherIds as $tid) {
                if (!in_array($tid, $teacherIds)) {
                    $teacherIds[] = $tid;
                }
            }
            
            if (!empty($teacherIds)) {
                // Normalisasi ke integer & unik agar cocok saat difilter di sisi JS.
                // Beberapa driver DB (MySQL/PDO) mengembalikan id sebagai string.
                $subjectTeachersMap[$subj->id] = array_values(array_unique(array_map('intval', $teacherIds)));
            }
        }

        $days = Schedule::DAYS;

        return view('tatausaha.schedules.edit', compact(
            'schoolClass', 'timeSlots', 'schedules', 'uniqueSubjects', 'teachers',
            'classAssignments', 'days', 'selectedYearId', 'subjectTeachersMap'
        ));
    }

    /**
     * Simpan jadwal (bulk upsert)
     */
    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'slots' => ['required', 'array'],
            'slots.*.day' => ['required', 'in:' . implode(',', Schedule::DAYS)],
            'slots.*.time_slot_id' => ['required', 'exists:time_slots,id'],
            'slots.*.subject_id' => ['nullable', function ($attribute, $value, $fail) {
                // Izinkan kosong atau penanda kegiatan non-mapel (Upacara)
                if ($value === '' || $value === null || $value === '__upacara__') {
                    return;
                }
                if (!Subject::whereKey($value)->exists()) {
                    $fail('Mata pelajaran tidak valid.');
                }
            }],
            'slots.*.teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $yearId = $data['academic_year_id'];

        // Hapus jadwal lama untuk kelas & tahun ini
        Schedule::where('academic_year_id', $yearId)
            ->where('class_id', $schoolClass->id)
            ->delete();

        // Insert jadwal baru
        foreach ($data['slots'] as $slot) {
            $subjectId = $slot['subject_id'] ?? null;
            $activity = null;

            // Penanda slot kegiatan non-mapel (mis. Upacara): tanpa mapel & guru
            if ($subjectId === '__upacara__') {
                $activity = 'Upacara';
                $subjectId = null;
            }

            if (!empty($subjectId) || $activity) {
                Schedule::create([
                    'academic_year_id' => $yearId,
                    'class_id' => $schoolClass->id,
                    'day' => $slot['day'],
                    'time_slot_id' => $slot['time_slot_id'],
                    'subject_id' => $subjectId ?: null,
                    'activity' => $activity,
                    'teacher_id' => $activity ? null : ($slot['teacher_id'] ?? null),
                ]);
            }
        }

        // Auto-generate pertemuan berdasarkan jadwal dan tahun ajar
        $meetingsCreated = $this->generateMeetingsFromSchedule($yearId, $schoolClass->id);

        $successMsg = 'Jadwal kelas ' . $schoolClass->name . ' berhasil disimpan.';
        if ($meetingsCreated > 0) {
            $successMsg .= ' ' . $meetingsCreated . ' pertemuan otomatis telah dibuat.';
        }

        return redirect()->route('tatausaha.schedules.edit', [
            'schoolClass' => $schoolClass->id,
            'academic_year_id' => $yearId,
        ])->with('success', $successMsg);
    }

    /**
     * Kosongkan seluruh jadwal untuk satu kelas & tahun ajar.
     */
    public function clear(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ]);

        $yearId = $data['academic_year_id'];

        Schedule::where('academic_year_id', $yearId)
            ->where('class_id', $schoolClass->id)
            ->delete();

        return redirect()->route('tatausaha.schedules.edit', [
            'schoolClass' => $schoolClass->id,
            'academic_year_id' => $yearId,
        ])->with('success', 'Semua jadwal kelas ' . $schoolClass->name . ' telah dikosongkan.');
    }

    /**
     * Halaman kelola jam pelajaran
     */
    public function timeSlots(Request $request): View
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeYear?->id ?? null);

        $timeSlots = collect();
        if ($selectedYearId) {
            $timeSlots = TimeSlot::where('academic_year_id', $selectedYearId)
                ->orderBy('slot_order')
                ->get();

            // Buat default jika belum ada
            if ($timeSlots->isEmpty()) {
                $this->seedDefaultTimeSlots($selectedYearId);
                $timeSlots = TimeSlot::where('academic_year_id', $selectedYearId)
                    ->orderBy('slot_order')
                    ->get();
            }
        }

        return view('tatausaha.schedules.time-slots', compact(
            'timeSlots', 'academicYears', 'selectedYearId'
        ));
    }

    /**
     * Simpan/tambah time slot
     */
    public function storeTimeSlot(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'slot_order' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:lesson,break'],
            'label' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        TimeSlot::updateOrCreate(
            [
                'academic_year_id' => $data['academic_year_id'],
                'slot_order' => $data['slot_order'],
            ],
            $data
        );

        return redirect()->back()->with('success', 'Jam pelajaran berhasil disimpan.');
    }

    /**
     * Hapus time slot
     */
    public function deleteTimeSlot(TimeSlot $timeSlot): RedirectResponse
    {
        $yearId = $timeSlot->academic_year_id;
        $timeSlot->delete();

        return redirect()->route('tatausaha.schedules.time-slots', [
            'academic_year_id' => $yearId,
        ])->with('success', 'Jam pelajaran berhasil dihapus.');
    }

    /**
     * View cetak jadwal
     */
    public function printSchedule(Request $request): View
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYearId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeYear?->id ?? null);

        $timeSlots = $selectedYearId
            ? TimeSlot::where('academic_year_id', $selectedYearId)->orderBy('slot_order')->get()
            : collect();

        $days = Schedule::DAYS;

        // Jika class_id diberikan, cetak hanya satu kelas
        if ($request->filled('class_id')) {
            $classes = SchoolClass::where('id', $request->class_id)->get();
        } else {
            $classes = SchoolClass::orderBy('name')->get();
        }

        // Ambil semua jadwal
        $allSchedules = $selectedYearId
            ? Schedule::where('academic_year_id', $selectedYearId)
                ->with(['subject', 'teacher.user'])
                ->get()
                ->groupBy('class_id')
            : collect();

        $academicYear = $selectedYearId ? AcademicYear::find($selectedYearId) : null;

        // Filter days: hanya tampilkan hari yang ada jadwalnya
        $activeDays = $days;
        if ($request->filled('active_days')) {
            $activeDays = explode(',', $request->active_days);
        }

        return view('tatausaha.schedules.print', compact(
            'classes', 'timeSlots', 'allSchedules', 'days', 'activeDays', 'academicYear'
        ));
    }

    /**
     * Auto-generate pertemuan berdasarkan jadwal dan rentang tahun ajar.
     * Menghitung semua tanggal dalam rentang tahun ajar yang sesuai
     * dengan hari-hari di jadwal, lalu membuat Meeting untuk masing-masing.
     *
     * @return int Jumlah pertemuan yang baru dibuat
     */
    private function generateMeetingsFromSchedule(int $yearId, int $classId): int
    {
        $academicYear = AcademicYear::find($yearId);

        // Jika tahun ajar tidak punya rentang tanggal, skip
        if (!$academicYear || !$academicYear->start_date || !$academicYear->end_date) {
            return 0;
        }

        // Mapping nama hari Indonesia ke Carbon dayOfWeekIso (1=Senin, 6=Sabtu)
        $dayMap = [
            'Senin'  => 1,
            'Selasa' => 2,
            'Rabu'   => 3,
            'Kamis'  => 4,
            'Jumat'  => 5,
            'Sabtu'  => 6,
        ];

        // Ambil semua jadwal untuk kelas ini yang memiliki mapel & guru
        $schedules = Schedule::where('academic_year_id', $yearId)
            ->where('class_id', $classId)
            ->whereNotNull('subject_id')
            ->whereNotNull('teacher_id')
            ->get();

        if ($schedules->isEmpty()) {
            return 0;
        }

        // Group by teacher_id + subject_id, kumpulkan hari-harinya
        $groups = [];
        foreach ($schedules as $schedule) {
            $key = $schedule->teacher_id . '_' . $schedule->subject_id;
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'teacher_id'  => $schedule->teacher_id,
                    'subject_id'  => $schedule->subject_id,
                    'days'        => [],
                ];
            }
            $dayIso = $dayMap[$schedule->day] ?? null;
            if ($dayIso && !in_array($dayIso, $groups[$key]['days'])) {
                $groups[$key]['days'][] = $dayIso;
            }
        }

        $startDate = Carbon::parse($academicYear->start_date);
        $endDate   = Carbon::parse($academicYear->end_date);
        $created   = 0;

        foreach ($groups as $group) {
            // Generate semua tanggal dalam rentang yang jatuh pada hari-hari tersebut
            $dates = [];
            $current = $startDate->copy();

            while ($current->lte($endDate)) {
                if (in_array($current->dayOfWeekIso, $group['days'])) {
                    $dates[] = $current->copy();
                }
                $current->addDay();
            }

            if (empty($dates)) {
                continue;
            }

            // Cari nomor pertemuan terakhir yang sudah ada
            $lastNumber = Meeting::where('teacher_id', $group['teacher_id'])
                ->where('class_id', $classId)
                ->where('subject_id', $group['subject_id'])
                ->max('number') ?? 0;

            // Buat pertemuan untuk setiap tanggal yang belum ada
            foreach ($dates as $date) {
                $exists = Meeting::where('teacher_id', $group['teacher_id'])
                    ->where('class_id', $classId)
                    ->where('subject_id', $group['subject_id'])
                    ->where('date', $date->toDateString())
                    ->exists();

                if (!$exists) {
                    $lastNumber++;
                    Meeting::create([
                        'teacher_id' => $group['teacher_id'],
                        'class_id'   => $classId,
                        'subject_id' => $group['subject_id'],
                        'title'      => 'Pertemuan ' . $lastNumber,
                        'date'       => $date->toDateString(),
                        'number'     => $lastNumber,
                    ]);
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Seed default time slots untuk tahun ajar
     */
    private function seedDefaultTimeSlots(int $yearId): void
    {
        $defaults = [
            ['slot_order' => 1,  'type' => 'lesson', 'label' => 'Jam ke-1',    'start_time' => '07:30', 'end_time' => '08:15'],
            ['slot_order' => 2,  'type' => 'lesson', 'label' => 'Jam ke-2',    'start_time' => '08:15', 'end_time' => '09:00'],
            ['slot_order' => 3,  'type' => 'lesson', 'label' => 'Jam ke-3',    'start_time' => '09:00', 'end_time' => '09:45'],
            ['slot_order' => 4,  'type' => 'break',  'label' => 'Istirahat 1', 'start_time' => '09:45', 'end_time' => '10:00'],
            ['slot_order' => 5,  'type' => 'lesson', 'label' => 'Jam ke-4',    'start_time' => '10:00', 'end_time' => '10:45'],
            ['slot_order' => 6,  'type' => 'lesson', 'label' => 'Jam ke-5',    'start_time' => '10:45', 'end_time' => '11:30'],
            ['slot_order' => 7,  'type' => 'lesson', 'label' => 'Jam ke-6',    'start_time' => '11:30', 'end_time' => '12:15'],
            ['slot_order' => 8,  'type' => 'break',  'label' => 'Istirahat 2', 'start_time' => '12:15', 'end_time' => '12:45'],
            ['slot_order' => 9,  'type' => 'lesson', 'label' => 'Jam ke-7',    'start_time' => '12:45', 'end_time' => '13:30'],
            ['slot_order' => 10, 'type' => 'lesson', 'label' => 'Jam ke-8',    'start_time' => '13:30', 'end_time' => '14:15'],
            ['slot_order' => 11, 'type' => 'lesson', 'label' => 'Jam ke-9',    'start_time' => '14:15', 'end_time' => '15:00'],
            ['slot_order' => 12, 'type' => 'lesson', 'label' => 'Jam ke-10',   'start_time' => '15:00', 'end_time' => '15:45'],
        ];

        foreach ($defaults as $slot) {
            TimeSlot::create(array_merge($slot, ['academic_year_id' => $yearId]));
        }
    }
}
