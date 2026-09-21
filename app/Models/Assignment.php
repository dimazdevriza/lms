<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'meeting_id',
        'type',
        'title',
        'description',
        'due_at',
        'file_path',
        'quiz_url',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function isOnline(): bool
    {
        return $this->type === 'online';
    }

    public function isExternal(): bool
    {
        return $this->type === 'external';
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class)->orderBy('created_at', 'desc');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Check if the teacher instruction physical file actually exists in server storage.
     */
    public function hasPhysicalFile(): bool
    {
        if (empty($this->file_path) || str_contains($this->file_path, '..')) {
            return false;
        }

        $clean = ltrim($this->file_path, '/\\');

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($clean) || \Illuminate\Support\Facades\Storage::disk('public')->exists($clean)) {
            return true;
        }

        $candidatePaths = [
            storage_path('app/' . $clean),
            storage_path('app/private/' . $clean),
            storage_path('app/public/' . $clean),
            public_path('storage/' . $clean),
            public_path($clean),
            storage_path($clean),
        ];

        foreach ($candidatePaths as $path) {
            if ($path && file_exists($path) && is_file($path)) {
                return true;
            }
        }

        return false;
    }
}

