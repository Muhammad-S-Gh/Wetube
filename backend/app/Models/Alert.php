<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\AlertReasonEnum;

class Alert extends Model
{
    protected $fillable = ['user_id', 'video_id', 'reason', 'description', 'resolved'];
    protected $casts = [
        'resolved' => 'boolean',
        'reason' => AlertReasonEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
