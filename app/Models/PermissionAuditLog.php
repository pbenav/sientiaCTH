<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionAuditLog extends Model
{
    use HasFactory;

    protected $table = 'permission_audit_log';
    public $timestamps = false; // Custom created_at only

    protected $fillable = [
        'user_id',
        'permission_name',
        'action',
        'result',
        'performed_by',
        'team_id',
        'resource_type',
        'resource_id',
        'context',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
