<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'member_number',
        'name',
        'email',
        'phone',
        'gender',
        'birthdate',
        'address',
        'city',
        'province',
        'country',
        'profile_picture',
        'regional_id',
        'status',
        'description',
    ];

    protected $appends = ['profile_picture_url'];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'member_number' => 'integer',
        ];
    }

    /**
     * Get the profile picture URL (full URL with domain).
     */
    protected function profilePictureUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile_picture
                ? url(Storage::url('profile-pictures/' . $this->profile_picture))
                : null,
        );
    }

    /**
     * Get the user that owns the member profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
