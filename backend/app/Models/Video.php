<?php

namespace App\Models;

use App\Enums\UploadStateEnum;
use App\Services\CloudinaryMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{

    protected $guarded = [];

    protected $casts = [
        'processed' => UploadStateEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function primaryVideo(): ?Media
    {
        return $this->relationLoaded('media')
            ? $this->media->firstWhere('resource_type', 'video')
            : $this->media()->where('resource_type', 'video')->first();
    }

    public function thumbnailMedia(): ?Media
    {
        return $this->relationLoaded('media')
            ? $this->media->firstWhere('resource_type', 'image')
            : $this->media()->where('resource_type', 'image')->first();
    }

    public function thumbnailDisplayUrl(): ?string
    {
        $thumbnail = $this->thumbnailMedia();

        if (!$thumbnail) {
            return null;
        }

        return app(CloudinaryMediaService::class)->imageDisplayUrl($thumbnail);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function viewers()
    {
        return $this->belongsToMany(User::class, 'history', 'video_id', 'user_id')
            ->withTimestamps()->withPivot('id'); // To show watch-time
    }

    public function views()
    {
        return $this->hasMany(History::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "{%$search%}")
                ->orWhereHas('user', function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }
}
