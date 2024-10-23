<?php

namespace Rapid\Mmb\PanelKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Rapid\Mmb\PanelKit\Database\Factories\LockStatusFactory;

class LockStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'lock_require_id',
        'unique_id',
        'passed_at',
    ];

    protected $casts = [
        'passed_at' => 'datetime',
    ];
}
