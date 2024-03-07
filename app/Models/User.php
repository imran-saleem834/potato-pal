<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    const CATEGORY_TYPES = ['buyer-group', 'grower-group', 'cool-store'];

    const CATEGORY_INPUTS = ['buyer_group', 'grower_group', 'cool_store'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'phone',
        'role',
        'password',
        'email_verified_at',
        'grower_name',
        'grower_tags',
        'buyer_name',
        'buyer_tags',
        'paddocks',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role'              => 'array',
        'grower_tags'       => 'array',
        'buyer_tags'        => 'array',
        'paddocks'          => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return ! is_null($this->email_verified_at) && ! is_null($this->role);
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(CategoriesRelation::class, 'categorizable');
    }

    public function receivals(): HasMany
    {
        return $this->hasMany(Receival::class, 'grower_id');
    }

    public function remainingReceivals(): HasMany
    {
        return $this->hasMany(RemainingReceival::class, 'grower_id');
    }
}
