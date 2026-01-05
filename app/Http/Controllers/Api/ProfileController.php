<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Get user profile by user ID.
     *
     * Fetches user data joined with member data.
     */
    public function getByUserId(string $userId): JsonResponse
    {
        // Validate that user_id is a positive integer
        if (!ctype_digit($userId) || (int) $userId <= 0) {
            return response()->json([
                'message' => 'Invalid user ID. Must be a positive integer.',
                'errors' => [
                    'user_id' => ['The user ID must be a positive integer.']
                ]
            ], 422);
        }

        $user = User::with('member')->find((int) $userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'errors' => [
                    'user_id' => ['No user found with the provided ID.']
                ]
            ], 404);
        }

        $member = $user->member;

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found for this user.',
                'errors' => [
                    'user_id' => ['No member profile associated with this user.']
                ]
            ], 404);
        }

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'member_number' => $member->member_number,
                'full_name' => $member->name,
                'email' => $user->email,
                'phone' => $member->phone,
                'birthdate' => $member->birthdate?->format('Y-m-d'),
                'address' => $member->address,
                'city' => $member->city,
                'province' => $member->province,
                'country' => $member->country,
                'profile_picture_url' => $member->profile_picture_url,
                'status' => $member->status,
                'created_at' => $member->created_at?->toISOString(),
                'updated_at' => $member->updated_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Get member by member number.
     *
     * Fetches member data by member_number field.
     */
    public function getByMemberNumber(string $memberNumber): JsonResponse
    {
        // Validate that member_number is a positive integer
        if (!ctype_digit($memberNumber) || (int) $memberNumber <= 0) {
            return response()->json([
                'message' => 'Invalid member number. Must be a positive integer.',
                'errors' => [
                    'member_number' => ['The member number must be a positive integer.']
                ]
            ], 422);
        }

        $member = Member::where('member_number', (int) $memberNumber)->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member not found.',
                'errors' => [
                    'member_number' => ['No member found with the provided member number.']
                ]
            ], 404);
        }

        return response()->json([
            'data' => [
                'user_id' => $member->user_id,
                'member_number' => $member->member_number,
                'full_name' => $member->name,
                'status' => $member->status,
                'updated_at' => $member->updated_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Update authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $member = $user->member;

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found for this user.',
                'errors' => [
                    'user' => ['No member profile associated with this user.']
                ]
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'birthdate' => ['sometimes', 'nullable', 'date', 'before:today'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'province' => ['sometimes', 'nullable', 'string', 'max:100'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile_picture' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ], [
            'profile_picture.image' => 'The file must be a valid image.',
            'profile_picture.mimes' => 'The image must be a file of type: jpeg, jpg, png, gif, webp.',
            'profile_picture.max' => 'The image must not be larger than 5 MB.',
            'birthdate.before' => 'The birthdate must be a date before today.',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($member->profile_picture) {
                Storage::disk('public')->delete('profile-pictures/' . $member->profile_picture);
            }

            $file = $request->file('profile_picture');
            $extension = $file->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;

            // Store the file
            $file->storeAs('profile-pictures', $filename, 'public');

            $member->profile_picture = $filename;
        }

        // Update profile fields
        if (isset($validated['full_name'])) {
            $member->name = $validated['full_name'];
        }
        if (array_key_exists('phone', $validated)) {
            $member->phone = $validated['phone'];
        }
        if (array_key_exists('birthdate', $validated)) {
            $member->birthdate = $validated['birthdate'];
        }
        if (array_key_exists('address', $validated)) {
            $member->address = $validated['address'];
        }
        if (array_key_exists('city', $validated)) {
            $member->city = $validated['city'];
        }
        if (array_key_exists('province', $validated)) {
            $member->province = $validated['province'];
        }
        if (array_key_exists('country', $validated)) {
            $member->country = $validated['country'];
        }

        $member->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => [
                'user_id' => $user->id,
                'member_number' => $member->member_number,
                'full_name' => $member->name,
                'email' => $user->email,
                'phone' => $member->phone,
                'birthdate' => $member->birthdate?->format('Y-m-d'),
                'address' => $member->address,
                'city' => $member->city,
                'province' => $member->province,
                'country' => $member->country,
                'profile_picture_url' => $member->profile_picture_url,
                'status' => $member->status,
                'updated_at' => $member->updated_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Delete profile picture.
     */
    public function deleteProfilePicture(Request $request): JsonResponse
    {
        $user = $request->user();
        $member = $user->member;

        if (!$member) {
            return response()->json([
                'message' => 'Member profile not found for this user.',
                'errors' => [
                    'user' => ['No member profile associated with this user.']
                ]
            ], 404);
        }

        if (!$member->profile_picture) {
            return response()->json([
                'message' => 'No profile picture to delete.',
            ], 404);
        }

        // Delete the file
        Storage::disk('public')->delete('profile-pictures/' . $member->profile_picture);

        $member->profile_picture = null;
        $member->save();

        return response()->json([
            'message' => 'Profile picture deleted successfully',
        ]);
    }
}
