<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkDay extends Model
{
    protected $guarded = [];

    public function officer():BelongsTo{
        return $this->belongsTo(Officer::class);
    }

}
