<?php

namespace Database\Seeders;

use App\Models\StaffShiftRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BloodBankSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedStaff();
        });
    }

    private function seedStaff(): void
    {
        $accounts = [
            ['Kane Sajol', 'kane_sajol', 'kane.sajol@crh-bloodbank.gov.ph', 'Medical Technologist', 'Morning Shift (6AM – 2PM)', 'morning', 'Central Screening Lab / Tubajon', '0918-123-4567', 'KS', '#2563eb', '2026-08-25 07:00 AM'],
            ['John Doe', 'john_doe', 'john.doe@crh-bloodbank.gov.ph', 'Blood Collection Nurse', 'Afternoon Shift (2PM – 10PM)', 'afternoon', 'Mobile Donation Center / San Jose', '0917-555-8899', 'JD', '#d97706', '2026-08-25 03:00 PM'],
            ['Jumel Alipao', 'jumel_alipao', 'jumel.alipao@crh-bloodbank.gov.ph', 'Emergency Screening Officer', 'Midnight Shift (10PM – 6AM)', 'midnight', 'Emergency Night Lab / Main Hospital', '0922-777-3311', 'JA', '#7c3aed', '2026-08-24 10:00 PM'],
            ['Sarah Ahmed', 'sarah_ahmed', 'sarah.ahmed@crh-bloodbank.gov.ph', 'Blood Bank Administrator', 'Morning Shift (6AM – 2PM)', 'morning', 'Administration & QC Office', '0919-888-4422', 'SA', '#059669', '2026-08-25 06:50 AM'],
            ['Maria Elena Santos', 'maria_santos', 'maria.santos@crh-bloodbank.gov.ph', 'Phlebotomist / Screener', 'Afternoon Shift (2PM – 10PM)', 'afternoon', 'Basilisa Collection Unit', '0920-111-2244', 'MS', '#dc2626', '2026-08-24 02:15 PM'],
        ];

        $users = [];
        foreach ($accounts as $account) {
            $users[$account[1]] = User::query()->create([
                'name' => $account[0],
                'username' => $account[1],
                'email' => $account[2],
                'password' => 'password',
                'role' => $account[3],
                'assigned_shift' => $account[4],
                'shift_type' => $account[5],
                'station' => $account[6],
                'contact' => $account[7],
                'status' => 'Active',
                'avatar_initials' => $account[8],
                'avatar_color' => $account[9],
                'last_login' => $account[10],
            ]);
        }

        $shifts = [
            ['kane_sajol', 'Morning Shift', '6:00 AM – 2:00 PM', 'morning', '2026-08-25', '07:00 AM', null, 'On Duty', 'Central Screening Lab - Terminal 1', 'Logged in for Morning Shift duties.'],
            ['john_doe', 'Afternoon Shift', '2:00 PM – 10:00 PM', 'afternoon', '2026-08-25', '03:00 PM', null, 'On Duty', 'San Jose Mobile Unit - Terminal 2', 'Logged in for Afternoon Shift collection.'],
            ['jumel_alipao', 'Midnight Shift', '10:00 PM – 6:00 AM', 'midnight', '2026-08-24', '10:00 PM', '06:00 AM', 'Completed', 'Emergency Night Lab - Terminal 3', 'Completed midnight emergency screening.'],
            ['sarah_ahmed', 'Morning Shift', '6:00 AM – 2:00 PM', 'morning', '2026-08-25', '06:50 AM', null, 'On Duty', 'Admin QC Office - Terminal 1', 'Admin morning quality inspection.'],
            ['maria_santos', 'Afternoon Shift', '2:00 PM – 10:00 PM', 'afternoon', '2026-08-24', '02:15 PM', '10:00 PM', 'Completed', 'Basilisa Collection Unit - Terminal 1', 'Completed afternoon phlebotomy duty.'],
            ['kane_sajol', 'Morning Shift', '6:00 AM – 2:00 PM', 'morning', '2026-08-24', '06:00 AM', '02:00 PM', 'Completed', 'Central Screening Lab - Terminal 1', 'Standard morning shift completed.'],
        ];

        foreach ($shifts as $shift) {
            StaffShiftRecord::query()->create([
                'user_id' => $users[$shift[0]]->id,
                'shift_name' => $shift[1],
                'shift_time_range' => $shift[2],
                'shift_type' => $shift[3],
                'login_date' => $shift[4],
                'login_time' => $shift[5],
                'logout_time' => $shift[6],
                'status' => $shift[7],
                'station' => $shift[8],
                'notes' => $shift[9],
            ]);
        }
    }
}
