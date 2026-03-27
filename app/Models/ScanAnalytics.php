<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ScanAnalytics extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'qr_code_id',
        'timestamp',
        'ip_address_hash',
        'user_agent',
        'country',
        'city',
        'device_type',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    public static function hashIp(?string $ipAddress): string
    {
        return hash('sha256', ($ipAddress ?? '').config('dynamicqr.ip_hash_salt'));
    }
}
