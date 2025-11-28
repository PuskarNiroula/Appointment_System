<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Officer extends Model
{
    protected $guarded = [];

    public function Post():BelongsTo{
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
    public function workDay():HasMany{
        return $this->hasMany(WorkDay::class);
    }
    public function activities():HasMany{
        return $this->hasMany(Activity::class);
    }
    public function appointment():HasMany{
        return $this->hasMany(Appointment::class);
    }
}
