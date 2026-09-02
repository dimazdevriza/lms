<?php

namespace Tests\Feature;

use App\Http\Controllers\ParentController;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ParentPortalSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_does_not_expose_parent_code(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'nisn' => 'NIS-10001',
            'phone' => null,
            'class_id' => null,
            'parent_code' => 'ORTU-ABCDEF1234',
        ]);

        $response = $this->actingAs($user)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertDontSee($student->parent_code, false);
    }

    public function test_parent_access_route_is_rate_limited(): void
    {
        $ip = '127.0.0.1';
        $code = 'ORTU-FAKE12345';
        $codeHash = hash('sha256', strtoupper($code));

        RateLimiter::clear('parent:access:ip:' . $ip);
        RateLimiter::clear('parent:access:code:' . $codeHash);

        for ($i = 0; $i < 5; $i++) {
            $response = $this
                ->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('parent.access'), [
                    'parent_code' => $code,
                ]);

            $response->assertStatus(302);
        }

        $blocked = $this
            ->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('parent.access'), [
                'parent_code' => $code,
            ]);

        $blocked->assertStatus(429);
    }

    public function test_parent_logs_in_once_and_is_remembered_via_persistent_cookie(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nisn' => 'NIS-10002',
            'parent_code' => 'KODE01',
        ]);

        // 1. Parent inputs code once
        $response = $this->post(route('parent.access'), [
            'parent_code' => 'KODE01',
        ]);

        $response->assertRedirect(route('parent.dashboard'));
        $response->assertCookie(ParentController::COOKIE_NAME);

        // Get the cookie value
        $cookie = $response->getCookie(ParentController::COOKIE_NAME);
        $this->assertNotNull($cookie);

        // 2. Clear PHP session to simulate browser restart / day after
        $this->flushSession();

        // 3. Parent visits index again with only the cookie -> should automatically redirect to dashboard without re-entering code
        $followUp = $this->withCookie(ParentController::COOKIE_NAME, $cookie->getValue())
            ->get(route('parent.index'));

        $followUp->assertRedirect(route('parent.dashboard'));

        // 4. Accessing dashboard directly with cookie works
        $dashResponse = $this->withCookie(ParentController::COOKIE_NAME, $cookie->getValue())
            ->get(route('parent.dashboard'));

        $dashResponse->assertOk();
    }

    public function test_parent_session_is_invalidated_when_code_changes_for_new_academic_year(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nisn' => 'NIS-10003',
            'parent_code' => 'KODE02',
        ]);

        // 1. Parent logs in with initial code
        $response = $this->post(route('parent.access'), [
            'parent_code' => 'KODE02',
        ]);
        $cookie = $response->getCookie(ParentController::COOKIE_NAME);

        // 2. New academic year / code regenerated
        $student->update(['parent_code' => 'KODE99']);

        // 3. Parent tries to access with old cookie
        $this->flushSession();
        $dashResponse = $this->withCookie(ParentController::COOKIE_NAME, $cookie->getValue())
            ->get(route('parent.dashboard'));

        // Should be redirected back to parent.index with error message
        $dashResponse->assertRedirect(route('parent.index'));
        $dashResponse->assertSessionHasErrors('parent_code');

        // 4. Entering new code works
        $newLogin = $this->post(route('parent.access'), [
            'parent_code' => 'KODE99',
        ]);
        $newLogin->assertRedirect(route('parent.dashboard'));
    }

    public function test_bulk_regenerate_codes_resets_all_student_codes(): void
    {
        $tuUser = User::factory()->create(['role' => 'tatausaha']);

        $user1 = User::factory()->create(['role' => 'siswa']);
        $student1 = Student::create(['user_id' => $user1->id, 'nisn' => 'NIS-101', 'parent_code' => 'OLDCD1']);

        $user2 = User::factory()->create(['role' => 'siswa']);
        $student2 = Student::create(['user_id' => $user2->id, 'nisn' => 'NIS-102', 'parent_code' => 'OLDCD2']);

        $response = $this->actingAs($tuUser)->post(route('parent.code.regenerate-all'));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $student1->refresh();
        $student2->refresh();

        $this->assertNotEquals('OLDCD1', $student1->parent_code);
        $this->assertNotEquals('OLDCD2', $student2->parent_code);
        $this->assertEquals(6, strlen($student1->parent_code));
        $this->assertEquals(6, strlen($student2->parent_code));
    }
}
