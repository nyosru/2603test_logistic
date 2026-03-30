<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    protected $table = 'slot';

    protected $primaryKey = 'slot_id';

    public $timestamps = false;

    protected $keyType = 'int';

    public $incrementing = false;

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class, 'to_slot', 'slot_id');
    }
}
