<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssignmentSubmission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'answer_text',
        'submitted_at',
        'score',
        'feedback',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(QuestionAnswer::class, 'assignment_submission_id');
    }

    public function comments()
    {
        return $this->hasMany(SubmissionComment::class, 'assignment_submission_id')->orderBy('created_at', 'asc');
    }

    /**
     * Check if the physical file actually exists in server storage.
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

