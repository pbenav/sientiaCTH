<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'team_id',
        'context',
        'valid_from',
        'valid_until',
        'granted_by',
        'granted_at',
        'revoked_by',
        'revoked_at',
    ];

    protected $casts = [
        'context' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
