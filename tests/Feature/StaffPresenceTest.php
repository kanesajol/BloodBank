<?php

namespace Tests\Feature;

use App\Models\StaffShiftRecord;
use App\Models\User;
use App\Support\StaffPresence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StaffPresenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        URL::forceRootUrl('http://localhost');
    }

    public function test_login_and_logout_are_stored_for_the_staff_account(): void
    {
        $user = User::factory()->create([
            'username' => 'staff_one',
            'password' => 'password',
        ]);

        $this->post('/login', [
            'username' => 'staff_one',
            'password' => 'password',
        ])->assertRedirect();

        $record = $user->shiftRecords()->first();
        $this->assertNotNull($record);
        $this->assertNull($record->logout_time);
        $this->assertNotNull($record->logged_in_at);

        $this->actingAs($user)->post('/logout')->assertRedirect();

        $record->refresh();
        $this->assertNotNull($record->logout_time);
        $this->assertSame('Completed', $record->status);
    }

    public function test_a_brownout_keeps_the_last_saved_time_as_logout(): void
    {
        $user = User::factory()->create();
        $seen = now()->subMinutes(10);

        $record = StaffShiftRecord::query()->create([
            'user_id' => $user->id,
            'shift_name' => 'Morning Shift',
            'shift_time_range' => '6:00 AM – 2:00 PM',
            'shift_type' => 'morning',
            'login_date' => $seen->toDateString(),
            'login_time' => $seen->format('h:i A'),
            'logged_in_at' => $seen,
            'logout_time' => null,
            'last_seen_at' => $seen,
            'status' => 'On Duty',
            'station' => $user->station,
        ]);

        StaffPresence::closeAbandoned();

        $record->refresh();
        $this->assertSame('Completed', $record->status);
        $this->assertSame($seen->format('Y-m-d h:i A'), $record->logged_out_at->format('Y-m-d h:i A'));
    }

    public function test_staff_can_sign_up_and_land_on_blood_collection(): void
    {
        $this->get('/signup')->assertOk()->assertSee('Staff Sign Up')->assertDontSee('Assigned Shift Schedule');

        $this->post('/signup', [
            'full_name' => 'Nerissa Kane',
            'username' => 'nerissa_kane',
            'role' => 'Medical Technologist',
            'shift_type' => 'morning',
            'contact' => '09181234567',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ])->assertRedirect('/dashboard/blood-collection');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'nerissa_kane',
            'role' => 'Medical Technologist',
            'station' => '',
        ]);
    }

    public function test_staff_can_edit_their_profile_and_change_password_from_the_web_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Profile Staff',
            'username' => 'profile_staff',
            'password' => 'secret123',
            'email' => 'profile_staff@staff.local',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/profile')
            ->assertOk()
            ->assertSee('Edit Profile')
            ->assertSee('Change Password');

        $this->actingAs($user)
            ->patch('/dashboard/profile', [
                'name' => 'Updated Profile Staff',
                'email' => 'updated-profile@example.com',
                'contact' => '09181234567',
                'role' => 'Blood Collection Nurse',
            ])
            ->assertRedirect(route('staff-profile'))
            ->assertSessionHas('success', 'Profile updated.');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Profile Staff',
            'email' => 'updated-profile@example.com',
            'contact' => '09181234567',
            'role' => 'Blood Collection Nurse',
        ]);

        $this->actingAs($user)
            ->patch('/dashboard/profile/password', [
                'current_password' => 'secret123',
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ])
            ->assertRedirect(route('staff-profile'))
            ->assertSessionHas('success', 'Password changed.');

        $this->assertTrue(Hash::check('newsecret123', $user->fresh()->password));
    }

    public function test_username_can_be_a_gmail_address(): void
    {
        $this->post('/signup', [
            'full_name' => 'Kane Sajol',
            'username' => 'kane.sajol@gmail.com',
            'role' => 'Medical Technologist',
            'shift_type' => 'morning',
            'contact' => '09181234567',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
        ])->assertRedirect('/dashboard/blood-collection');

        $this->assertDatabaseHas('users', [
            'username' => 'kane.sajol@gmail.com',
            'email' => 'kane.sajol@gmail.com',
        ]);
    }

    public function test_date_search_lists_names_from_first_login_to_the_next(): void
    {
        $firstCreated = User::factory()->create(['name' => 'Ana Early']);
        $secondCreated = User::factory()->create(['name' => 'Ben Late']);
        $otherDay = User::factory()->create(['name' => 'Cara Other']);

        $this->storeLogin($secondCreated, '2026-09-20 07:05:00');
        $this->storeLogin($firstCreated, '2026-09-20 09:40:00');
        $this->storeLogin($otherDay, '2026-09-21 08:00:00');

        $response = $this->actingAs($firstCreated)->get('/dashboard/staff-log?date=2026-09-20');
        $response->assertOk();

        preg_match('/id="day-log-table".*?<\/tbody>/s', $response->getContent(), $match);
        $log = $match[0] ?? '';
        $ben = strpos($log, 'Ben Late');
        $ana = strpos($log, 'Ana Early');

        $this->assertNotFalse($ben);
        $this->assertNotFalse($ana);
        $this->assertLessThan($ana, $ben);
        $this->assertStringNotContainsString('Cara Other', $log);
    }

    private function storeLogin(User $user, string $at): void
    {
        $time = Carbon::parse($at);

        StaffShiftRecord::query()->create([
            'user_id' => $user->id,
            'shift_name' => 'Morning Shift',
            'shift_time_range' => '6:00 AM – 2:00 PM',
            'shift_type' => 'morning',
            'login_date' => $time->toDateString(),
            'login_time' => $time->format('h:i A'),
            'logged_in_at' => $time,
            'logout_time' => null,
            'status' => 'On Duty',
            'station' => '',
        ]);
    }

    public function test_staff_page_lists_each_login_and_logout(): void
    {
        $user = User::factory()->create();
        StaffPresence::open($user);
        StaffPresence::signOut($user);

        $response = $this->actingAs($user)->get('/dashboard/staff-accounts');

        $response->assertOk();
        $response->assertSee('Login / Logout');
        $response->assertDontSee('Assigned Shift Schedule');
        $response->assertDontSee('Station / Branch');
        $response->assertSee('All logins');
        $response->assertSee('Signed in');
        $closed = $user->shiftRecords()->whereNotNull('logout_time')->first();
        $response->assertSee($closed->logoutLabel(), false);
    }
}
