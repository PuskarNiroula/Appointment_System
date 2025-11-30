<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 * @method static where(string $string, string $string1)
 */
class Visitor extends Model
{
   protected $guarded = [];
   public function appointment(): HasMany{
       return $this->HasMany(Appointment::class);
   }

  protected $hidden = ['created_at','updated_at'];
}
