<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UpdatePlainPasswordsSeeder extends Seeder
{
    public function run(): void
    {
        // Update existing users with their known plain passwords
        $users = [
            'iheb@admin.com' => '12345678',
            'nour@gmail.com' => 'nouramara',
            'aaaa@dev.com' => 'nouramara',
            'admin@example.com' => 'password',
            'packaging@example.com' => 'password',
            'client@example.com' => 'password',
            'test@example.com' => 'password',
        ];

        foreach ($users as $email => $plainPassword) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['plain_password' => $plainPassword]);
                $this->command->info("✅ Updated plain password for {$email}");
            }
        }

        $this->command->info('✅ All known passwords updated for super admin viewing');
    }
}
