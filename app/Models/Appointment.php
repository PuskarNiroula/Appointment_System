<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 * @method static get()
 * @method static find(int $id)
 * @method static findOrFail(int $id)
 * @method static where(string $string, int $id)
 * @method static count()
 * @method static orderBy(string $string)
 * @method static select(string $string, string $string1, string $string2, string $string3, string $string4, string $string5)
 * @method static whereIn(string $string, $pluck)
 */
class Appointment extends Model
{

    protected $guarded = [];

    public function visitor(): BelongsTo{
        return $this->belongsTo(Visitor::class);
    }
    public function officer(): BelongsTo{
        return $this->belongsTo(Officer::class);
    }

    protected $hidden = ['created_at','updated_at'];

}
