<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberCardController extends Controller
{
    public function show(string $memberNumber)
    {
        if (!ctype_digit($memberNumber) || (int) $memberNumber <= 0) {
            return response()->view('member-card', [
                'member' => null,
                'error' => 'Invalid member number.',
            ], 404);
        }

        $member = Member::where('member_number', (int) $memberNumber)->first();

        if (!$member) {
            return response()->view('member-card', [
                'member' => null,
                'error' => 'Member not found.',
            ], 404);
        }

        return view('member-card', [
            'member' => $member,
            'error' => null,
        ]);
    }
}
