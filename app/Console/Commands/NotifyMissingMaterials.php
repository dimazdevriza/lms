<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\Notification;
use App\Models\Teacher;
use Illuminate\Console\Command;

class NotifyMissingMaterials extends Command
{
    protected $signature = 'lms:notify-missing-materials';
    protected $description = 'Kirim notifikasi in-app ke guru yang materi pembelajarannya memiliki berkas hilang di server';

    public function handle(): int
    {
        $materials = Material::with(['teacher.user', 'schoolClass', 'subject', 'meeting'])
            ->whereNotNull('file_path')
            ->get()
            ->filter(function ($m) {
                return !empty($m->file_path) && !$m->hasPhysicalFile();
            });

        $this->info("Ditemukan {$materials->count()} materi dengan berkas fisik hilang.");

        $sentCount = 0;

        foreach ($materials as $material) {
            $teacher = $material->teacher;
            if (!$teacher || !$teacher->user_id) {
                continue;
            }

            $className = $material->schoolClass?->name ?? $material->meeting?->schoolClass?->name ?? 'Kelas';
            $subjectName = $material->subject?->name ?? $material->meeting?->subject?->name ?? 'Mata Pelajaran';
            $meetingInfo = $material->meeting ? " (Pertemuan {$material->meeting->number})" : '';

            $title = "📚 Berkas Materi Perlu Diupload Ulang: {$material->title}";
            $message = "Berkas PDF materi '{$material->title}' untuk {$className} - {$subjectName}{$meetingInfo} belum tersimpan di server baru. Silakan klik untuk mengunggah kembali berkas materi Anda.";
            $url = route('guru.materials.edit', $material->id);

            // Avoid duplicate active unread notification
            $exists = Notification::where('user_id', $teacher->user_id)
                ->where('url', $url)
                ->whereNull('read_at')
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $teacher->user_id,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                ]);
                $sentCount++;
                $this->line("Notifikasi dikirim ke {$teacher->user?->name} untuk materi #{$material->id}");
            } else {
                $this->line("Notifikasi sudah ada (belum dibaca) untuk {$teacher->user?->name} materi #{$material->id}");
            }
        }

        $this->info("Selesai! Total {$sentCount} notifikasi baru berhasil dikirim.");
        return 0;
    }
}
