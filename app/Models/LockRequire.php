<?php

namespace Rapid\Mmb\PanelKit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Rapid\Mmb\PanelKit\Database\Factories\LockRequireFactory;

class LockRequire extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'is_public',
        'chat_id',
        'url',
        'title',
        'is_fake',
        'cache_pass',
        'accept_delay',
        'member_limit_until',
        'expire_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
    ];
}
