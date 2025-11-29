<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static get()
 */
class Activity extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    public function officer():BelongsTo{
        return $this->belongsTo(Officer::class);
    }

}
