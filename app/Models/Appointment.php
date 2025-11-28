<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{

    protected $guarded = [];

    public function visitor(): BelongsTo{
        return $this->belongsTo(Visitor::class);
    }
    public function officer(): BelongsTo{
        return $this->belongsTo(Officer::class);
    }

}
