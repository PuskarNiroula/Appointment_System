<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static create(array $array)
 * @method static findOrFail(int $id)
 * @method static select(string $string, string $string1, string $string2)
 * @method static where(string $string, string $string1)
 * @method pluck(string $string, string $string1)
 * @method static find(int $id)
 */
class Post extends Model
{
    protected $guarded = [];

    public function officer(): HasMany{
        return $this->hasMany(Officer::class);
    }
}
