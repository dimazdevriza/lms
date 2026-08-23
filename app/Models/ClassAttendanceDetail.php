<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassAttendanceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_attendance_id',
        'student_id',
        'status',
        'note',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(ClassAttendance::class, 'class_attendance_id');
    }

    public function classAttendance(): BelongsTo
    {
        return $this->belongsTo(ClassAttendance::class, 'class_attendance_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
