<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileDownloadController extends Controller
{
    /**
     * Download or stream the teacher's assignment questions file.
     */
    public function downloadAssignment(Assignment $assignment)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Admin can access all assignments
        } elseif ($user->role === 'guru') {
            $teacherId = Teacher::where('user_id', $user->id)->value('id');
            abort_unless($assignment->teacher_id == $teacherId, 403, 'Unauthorized.');
        } elseif ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            $classId = $assignment->class_id ?? $assignment->meeting?->class_id;
            abort_unless($classId == $student->class_id, 403, 'Unauthorized.');
        } else {
            abort(403, 'Unauthorized.');
        }

        $filePath = $assignment->file_path;
        $ext = pathinfo($filePath ?? '', PATHINFO_EXTENSION) ?: 'pdf';
        $fileName = Str::slug($assignment->title) . '_Instruksi.' . $ext;

        return $this->resolveFileResponse($filePath, $fileName, $assignment->teacher?->user?->name ?? 'Guru');
    }

    /**
     * Download or stream the student's submission file.
     */
    public function downloadSubmission(AssignmentSubmission $submission)
    {
        $user = Auth::user();
        $assignment = $submission->assignment;

        if ($user->role === 'admin') {
            // Admin can access all student submissions
        } elseif ($user->role === 'guru') {
            $teacherId = Teacher::where('user_id', $user->id)->value('id');
            abort_unless($assignment->teacher_id == $teacherId, 403, 'Unauthorized.');
        } elseif ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            abort_unless($submission->student_id == $student->id, 403, 'Unauthorized.');
        } else {
            abort(403, 'Unauthorized.');
        }

        $filePath = $submission->file_path;
        $ext = pathinfo($filePath ?? '', PATHINFO_EXTENSION) ?: 'pdf';
        $studentName = Str::slug($submission->student?->user?->name ?? 'Siswa');
        $assignmentTitle = Str::slug($assignment->title ?? 'Tugas');
        $fileName = "{$studentName}_{$assignmentTitle}.{$ext}";

        return $this->resolveFileResponse($filePath, $fileName, $submission->student?->user?->name ?? 'Siswa');
    }

    /**
     * View or stream a material file.
     */
    public function downloadMaterial(Material $material)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Admin can access all materials
        } elseif ($user->role === 'guru') {
            $teacherId = Teacher::where('user_id', $user->id)->value('id');
            abort_unless($material->teacher_id == $teacherId, 403, 'Unauthorized.');
        } elseif ($user->role === 'siswa') {
            $student = Student::where('user_id', $user->id)->firstOrFail();
            $classId = $material->class_id ?? $material->meeting?->class_id;
            abort_unless($classId == $student->class_id, 403, 'Unauthorized.');
        } else {
            abort(403, 'Unauthorized.');
        }

        $filePath = $material->file_path;
        $ext = pathinfo($filePath ?? '', PATHINFO_EXTENSION) ?: 'pdf';
        $fileName = Str::slug($material->title) . '.' . $ext;

        return $this->resolveFileResponse($filePath, $fileName, $material->teacher?->user?->name ?? 'Guru');
    }

    /**
     * Robust file resolver across all potential storage disks, paths, and legacy folders.
     */
    private function resolveFileResponse(?string $filePath, string $fileName, string $ownerName = '')
    {
        if (!$filePath || str_contains($filePath, '..')) {
            return $this->fileNotFoundResponse('Path berkas kosong atau tidak valid.', $ownerName, $filePath);
        }

        $clean = ltrim($filePath, '/\\');

        $headers = [
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ];

        // 1. Check Laravel storage disks directly (handles fake storage in tests and configured disk roots)
        if (Storage::disk('local')->exists($clean)) {
            return response()->file(Storage::disk('local')->path($clean), $headers);
        }

        if (Storage::disk('public')->exists($clean)) {
            return response()->file(Storage::disk('public')->path($clean), $headers);
        }

        // 2. Candidate real paths on disk
        // Handles Laravel 10 vs 11 storage root changes (app/ vs app/private/), public/storage symlinks, etc.
        $candidatePaths = [
            storage_path('app/' . $clean),
            storage_path('app/private/' . $clean),
            storage_path('app/public/' . $clean),
            public_path('storage/' . $clean),
            public_path($clean),
            storage_path($clean),
            base_path($clean),
        ];

        if (str_starts_with($clean, 'public/')) {
            $stripped = substr($clean, 7);
            $candidatePaths[] = storage_path('app/public/' . $stripped);
            $candidatePaths[] = storage_path('app/' . $stripped);
            $candidatePaths[] = storage_path('app/private/' . $stripped);
            $candidatePaths[] = public_path('storage/' . $stripped);
            $candidatePaths[] = public_path($stripped);
        }

        if (str_starts_with($clean, 'storage/')) {
            $stripped = substr($clean, 8);
            $candidatePaths[] = storage_path('app/public/' . $stripped);
            $candidatePaths[] = storage_path('app/' . $stripped);
            $candidatePaths[] = storage_path('app/private/' . $stripped);
            $candidatePaths[] = public_path('storage/' . $stripped);
            $candidatePaths[] = public_path($stripped);
        }

        if (str_starts_with($clean, 'private/')) {
            $stripped = substr($clean, 8);
            $candidatePaths[] = storage_path('app/private/' . $stripped);
            $candidatePaths[] = storage_path('app/' . $stripped);
            $candidatePaths[] = storage_path('app/' . $clean);
        }

        foreach ($candidatePaths as $path) {
            if ($path && file_exists($path) && is_file($path)) {
                return response()->file($path, $headers);
            }
        }

        // 3. Log warning for debugging
        Log::warning('Berkas tidak ditemukan di penyimpanan server', [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'owner' => $ownerName,
            'checked_paths' => array_values(array_unique(array_filter($candidatePaths))),
        ]);

        return $this->fileNotFoundResponse("Berkas '{$fileName}' tidak ditemukan pada sistem penyimpanan server.", $ownerName, $filePath);
    }

    /**
     * User-friendly 404 response page when a file is physically missing.
     */
    private function fileNotFoundResponse(string $message, string $ownerName = '', ?string $filePath = null)
    {
        return response()->view('errors.file-not-found', [
            'message' => $message,
            'ownerName' => $ownerName,
            'filePath' => $filePath,
        ], 404);
    }
}
