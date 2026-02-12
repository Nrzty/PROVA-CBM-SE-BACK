<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Occurrence extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    protected $fillable = [
        'external_id',
        'type',
        'status',
        'description',
        'reported_at',
    ];

    public function dispatches(): HasMany{
        return $this->hasMany(Dispatche::class);
    }
}
