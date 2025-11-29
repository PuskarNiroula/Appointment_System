<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 */
class Visitor extends Model
{
   protected $guarded = [];
   public function appointment(): HasMany{
       return $this->HasMany(Appointment::class);
   }

   public function getHidden()
   {
       return ['created_at','updated_at'];
   }
}
