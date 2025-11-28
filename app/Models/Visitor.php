<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
   protected $guarded = [];
   public function appointment(): HasMany{
       return $this->HasMany(Appointment::class);
   }
}
