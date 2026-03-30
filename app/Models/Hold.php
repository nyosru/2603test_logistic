<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hold extends Model
{
    protected $table = 'holds';

    public $timestamps = false;

    protected $fillable = [
        'to_slot',
        'at_end',
        'status',
        'UUID',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class, 'to_slot', 'slot_id');
    }
}
