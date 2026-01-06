<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'role' => '["admin","member"]',
            ],
            [
                'name' => 'Miaz akemapa',
                'email' => 'miazakemapa@gmail.com',
                'password' => Hash::make('password'),
                'role' => '["member"]',
            ],
            [
                'name' => 'Johny doe',
                'email' => 'hanzoster@gmail.com',
                'password' => Hash::make('password'),
                'role' => '["member"]',
            ],
            [
                'name' => 'Andy',
                'email' => 'andy@jonimail.com',
                'password' => Hash::make('password'),
                'role' => '["guest"]',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'role' => '["guest"]',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
