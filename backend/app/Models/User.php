<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function history()
    {
        return $this->hasMany(History::class);
    }

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function alerts()
    {
        return $this->hasOne(Alert::class); // user has one alert number
    }

    public function videoInHistory()
    {
        return $this->belongsToMany(Video::class, 'history', 'user_id', 'video_id')
            ->withTimestamps()->withPivot('id'); // to show watch-time
    }

    public function profilePicture()
    {
        return $this->morphOne(Media::class, 'mediable')->where('resource_type', 'image');
    }

    public function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function () {

            $path = $this->profile_photo_path;

            if ($path && Storage::disk($this->profilePhotoDisk())->exists($path)) {
                return Storage::url($path);
            }

            return $this->defaultProfilePhotoUrl();
        });
    }

    public function defaultProfilePhotoUrl()
    {
        return 'https://ui-avatars.com/api/?name='
            . urlencode($this->name)
            . '&length=1&background=7F9CF5&color=FFFFFF&size=200';
    }

    /**
     * Get users that match search criteria
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }
}
