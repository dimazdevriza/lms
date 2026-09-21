<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'meeting_id',
        'title',
        'content',
        'file_path',
        'youtube_url',
    ];

    public function getYoutubeEmbedUrlAttribute()
    {
        if (!$this->youtube_url) return null;

        $url = $this->youtube_url;
        $videoId = null;

        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
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

    /**
     * Check if the physical material file actually exists in server storage.
     */
    public function hasPhysicalFile(): bool
    {
        if (empty($this->file_path) || str_contains($this->file_path, '..')) {
            return false;
        }

        $clean = ltrim($this->file_path, '/\\');

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($clean) || \Illuminate\Support\Facades\Storage::disk('local')->exists($clean)) {
            return true;
        }

        $candidatePaths = [
            storage_path('app/public/' . $clean),
            storage_path('app/' . $clean),
            storage_path('app/private/' . $clean),
            public_path('storage/' . $clean),
            public_path($clean),
            storage_path($clean),
        ];

        if (str_starts_with($clean, 'public/')) {
            $stripped = substr($clean, 7);
            $candidatePaths[] = storage_path('app/public/' . $stripped);
            $candidatePaths[] = storage_path('app/' . $stripped);
            $candidatePaths[] = public_path('storage/' . $stripped);
        }

        if (str_starts_with($clean, 'storage/')) {
            $stripped = substr($clean, 8);
            $candidatePaths[] = storage_path('app/public/' . $stripped);
            $candidatePaths[] = storage_path('app/' . $stripped);
            $candidatePaths[] = public_path('storage/' . $stripped);
        }

        foreach ($candidatePaths as $path) {
            if ($path && file_exists($path) && is_file($path)) {
                return true;
            }
        }

        return false;
    }
}

