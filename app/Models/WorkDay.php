<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static insert(array $insertData)
 * @method static where(string $string, int $id)
 * @method static create(array $array)
 */
class WorkDay extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function officer():BelongsTo{
        return $this->belongsTo(Officer::class);
    }

}
