<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = [
            [
                'user_email' => 'miazakemapa@gmail.com',
                'member_number' => 327,
                'name' => 'Miaz Akemapa',
                'email' => 'miazakemapa@gmail.com',
                'phone' => '982121231231',
                'gender' => 'male',
                'birthdate' => '1983-05-14',
                'address' => 'Giri mekar permai B 104',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'country' => 'Indonesia',
                'status' => 'active',
            ],
            [
                'user_email' => 'hanzoster@gmail.com',
                'member_number' => 10223,
                'name' => 'John Doe',
                'email' => 'hanzoster@gmail.com',
                'phone' => null,
                'gender' => null,
                'birthdate' => null,
                'address' => null,
                'city' => null,
                'province' => null,
                'country' => null,
                'status' => 'inactive',
            ],
        ];

        foreach ($members as $memberData) {
            $user = User::where('email', $memberData['user_email'])->first();
            unset($memberData['user_email']);

            if ($user) {
                $memberData['user_id'] = $user->id;
            }

            Member::updateOrCreate(
                ['member_number' => $memberData['member_number']],
                $memberData
            );
        }
    }
}
