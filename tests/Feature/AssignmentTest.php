<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_external_assignment(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '1234567890',
            'phone' => '08123456789',
        ]);

        $class = SchoolClass::create(['name' => 'X IPA 1']);
        $subject = Subject::create(['name' => 'Matematika']);

        $response = $this->actingAs($user)->post(route('guru.assignments.store'), [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'type' => 'external',
            'title' => 'Kuis Quizizz Persamaan Kuadrat',
            'description' => 'Kerjakan kuis berikut.',
            'due_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'quiz_url' => 'https://quizizz.com/join?gc=123456',
        ]);

        $response->assertRedirect(route('guru.assignments.index'));

        $this->assertDatabaseHas('assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'type' => 'external',
            'title' => 'Kuis Quizizz Persamaan Kuadrat',
            'quiz_url' => 'https://quizizz.com/join?gc=123456',
        ]);
    }

    public function test_teacher_can_create_online_assignment_with_images(): void
    {
        $user = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'nip' => '1234567890',
            'phone' => '08123456789',
        ]);

        $class = SchoolClass::create(['name' => 'X IPA 1']);
        $subject = Subject::create(['name' => 'Matematika']);

        $questionsJson = json_encode([
            [
                'type' => 'pilihan_ganda',
                'body' => 'Berapakah nilai x?',
                'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
                'points' => 5,
                'options' => [
                    ['label' => 'A', 'body' => '1', 'image' => '', 'is_correct' => true],
                    ['label' => 'B', 'body' => '2', 'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'is_correct' => false],
                ]
            ]
        ]);

        $response = $this->actingAs($user)->post(route('guru.assignments.store'), [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'type' => 'online',
            'title' => 'Tugas Matematika Gambar',
            'description' => 'Kerjakan tugas berikut.',
            'due_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'questions_json' => $questionsJson,
        ]);

        $response->assertRedirect(route('guru.assignments.index'));

        $this->assertDatabaseHas('assignments', [
            'title' => 'Tugas Matematika Gambar',
            'type' => 'online',
        ]);

        $this->assertDatabaseHas('questions', [
            'body' => 'Berapakah nilai x?',
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'points' => 5,
        ]);

        $this->assertDatabaseHas('question_options', [
            'body' => '2',
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            'is_correct' => false,
        ]);
    }

    public function test_teacher_and_student_can_upload_doc_docx_xls_xlsx_files(): void
    {
        $teacherUser = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1234567890',
            'phone' => '08123456789',
        ]);

        $class = SchoolClass::create(['name' => 'X IPA 1']);
        $subject = Subject::create(['name' => 'Matematika']);

        // 1. Teacher uploads docx assignment
        $fakeDocx = \Illuminate\Http\UploadedFile::fake()->create('assignment.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response = $this->actingAs($teacherUser)->post(route('guru.assignments.store'), [
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'type' => 'pdf', // DB type name is still pdf but labeled as Document
            'title' => 'Tugas Menulis Laporan',
            'description' => 'Kerjakan di docx.',
            'due_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'file' => $fakeDocx,
        ]);

        $response->assertRedirect(route('guru.assignments.index'));
        $assignment = Assignment::where('title', 'Tugas Menulis Laporan')->first();
        $this->assertNotNull($assignment);
        $this->assertStringEndsWith('.docx', $assignment->file_path);

        // 2. Student uploads xlsx submission
        $studentUser = User::factory()->create(['role' => 'siswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nisn' => '111222',
            'class_id' => $class->id,
        ]);

        $fakeXlsx = \Illuminate\Http\UploadedFile::fake()->create('answer.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response = $this->actingAs($studentUser)->post(route('siswa.assignments.submit', $assignment), [
            'answer_text' => 'Ini jawaban saya.',
            'file' => $fakeXlsx,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'answer_text' => 'Ini jawaban saya.',
        ]);
    }

    public function test_teacher_and_admin_can_extend_assignment_deadline(): void
    {
        $teacherUser = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1234567890',
            'phone' => '08123456789',
        ]);

        $class = SchoolClass::create(['name' => 'X IPA 1']);
        $subject = Subject::create(['name' => 'Matematika']);

        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Tugas Matematika 1',
            'description' => 'Kerjakan soal 1-5.',
            'due_at' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'type' => 'pdf',
        ]);

        $newDeadline = now()->addDays(5)->format('Y-m-d\TH:i');

        $response = $this->actingAs($teacherUser)->post(route('guru.assignments.extend-deadline', $assignment), [
            'due_at' => $newDeadline,
        ]);

        $response->assertRedirect();
        $this->assertEquals(
            \Carbon\Carbon::parse($newDeadline)->format('Y-m-d H:i'),
            \Carbon\Carbon::parse($assignment->fresh()->due_at)->format('Y-m-d H:i')
        );
    }

    public function test_admin_and_teacher_can_download_student_submission(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $adminUser = User::factory()->create(['role' => 'admin']);
        $teacherUser = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1234567890',
            'phone' => '08123456789',
        ]);

        $class = SchoolClass::create(['name' => 'X IPA 1']);
        $subject = Subject::create(['name' => 'Matematika']);

        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Tugas Matematika 1',
            'type' => 'pdf',
        ]);

        $studentUser = User::factory()->create(['role' => 'siswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nisn' => '111222',
            'class_id' => $class->id,
        ]);

        $filePath = 'submissions/test_submission.pdf';
        \Illuminate\Support\Facades\Storage::disk('local')->put($filePath, 'fake content');

        $submission = \App\Models\AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'file_path' => $filePath,
        ]);

        $response = $this->actingAs($adminUser)->get(route('submissions.download', $submission));
        $response->assertOk();

        $responseGuru = $this->actingAs($teacherUser)->get(route('submissions.download', $submission));
        $responseGuru->assertOk();
    }

    public function test_student_can_edit_submission_before_deadline_and_cannot_after_deadline(): void
    {
        $teacherUser = User::factory()->create(['role' => 'guru']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'nip' => '1234567890',
            'phone' => '08123456789',
        ]);

        $class = SchoolClass::create(['name' => 'X IPA 1']);
        $subject = Subject::create(['name' => 'Matematika']);

        $assignment = Assignment::create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'title' => 'Tugas Edit Submission',
            'due_at' => now()->addDays(2),
            'type' => 'pdf',
        ]);

        $studentUser = User::factory()->create(['role' => 'siswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nisn' => '999888',
            'class_id' => $class->id,
        ]);

        // 1. Submit initial answer
        $response = $this->actingAs($studentUser)->post(route('siswa.assignments.submit', $assignment), [
            'answer_text' => 'Jawaban awal.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'answer_text' => 'Jawaban awal.',
        ]);

        // 2. Edit answer before deadline
        $response2 = $this->actingAs($studentUser)->post(route('siswa.assignments.submit', $assignment), [
            'answer_text' => 'Jawaban revisi sebelum deadline.',
        ]);
        $response2->assertRedirect();
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'answer_text' => 'Jawaban revisi sebelum deadline.',
        ]);

        // 3. Set deadline to past
        $assignment->update(['due_at' => now()->subDay()]);

        // 4. Try editing answer after deadline
        $response3 = $this->actingAs($studentUser)->post(route('siswa.assignments.submit', $assignment), [
            'answer_text' => 'Jawaban mencoba edit setelah deadline.',
        ]);
        $response3->assertSessionHasErrors(['general']);
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'answer_text' => 'Jawaban revisi sebelum deadline.',
        ]);
    }
}
