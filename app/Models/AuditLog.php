<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'meta' => 'array',
    ];

    protected $fillable = [
        'entity_id',
        'entity_type',
        'action',
        'before',
        'after',
        'meta',
    ];
}
