<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Notification;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialMissingFileTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_notifies_teacher_of_missing_material_file(): void
    {
        $teacherUser = User::factory()->create(['role' => 'guru', 'name' => 'Pak Guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1987654321',
            'phone' => '081234567890',
        ]);

        $class = SchoolClass::create(['name' => 'XI MIPA 1']);
        $subject = Subject::create(['name' => 'Fisika']);

        $material = Material::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Termodinamika Bagian 1',
            'file_path' => 'materials/ghost_file.pdf', // Doesn't exist physically
        ]);

        $this->assertFalse($material->hasPhysicalFile());

        // Run artisan command
        $this->artisan('lms:notify-missing-materials')
            ->expectsOutputToContain('Ditemukan 1 materi dengan berkas fisik hilang.')
            ->assertExitCode(0);

        // Check notification created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $teacherUser->id,
            'url' => route('guru.materials.edit', $material->id),
        ]);

        // Running again shouldn't duplicate unread notification
        $this->artisan('lms:notify-missing-materials')
            ->expectsOutputToContain('Notifikasi sudah ada (belum dibaca)')
            ->assertExitCode(0);

        $this->assertEquals(1, Notification::where('user_id', $teacherUser->id)->count());
    }

    public function test_teacher_dashboard_shows_missing_materials_count(): void
    {
        $teacherUser = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1987654322',
            'phone' => '081234567891',
        ]);

        $class = SchoolClass::create(['name' => 'X MIPA 2']);
        $subject = Subject::create(['name' => 'Kimia']);

        Material::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Ikatan Kimia',
            'file_path' => 'materials/missing_ikatan.pdf',
        ]);

        $response = $this->actingAs($teacherUser)->get(route('guru.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Ada 1 Berkas Materi yang Perlu Diunggah Ulang');
    }

    public function test_teacher_reuploading_material_file_clears_notification(): void
    {
        Storage::fake('public');

        $teacherUser = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1987654323',
            'phone' => '081234567892',
        ]);

        $class = SchoolClass::create(['name' => 'XII MIPA 3']);
        $subject = Subject::create(['name' => 'Biologi']);

        $material = Material::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Genetika',
            'file_path' => 'materials/missing_genetika.pdf',
        ]);

        $notification = Notification::create([
            'user_id' => $teacherUser->id,
            'title' => 'Materi Perlu Diupload',
            'message' => 'Silakan upload ulang',
            'url' => route('guru.materials.edit', $material->id),
        ]);

        $this->assertNull($notification->read_at);

        $fakePdf = UploadedFile::fake()->create('genetika_baru.pdf', 500, 'application/pdf');

        $response = $this->actingAs($teacherUser)->put(route('guru.materials.update', $material), [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Genetika (Revisi)',
            'file' => $fakePdf,
        ]);

        $response->assertRedirect(route('guru.materials.index'));

        $material->refresh();
        $this->assertNotEquals('materials/missing_genetika.pdf', $material->file_path);
        $this->assertTrue($material->hasPhysicalFile());

        // Check notification marked as read
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }
}
