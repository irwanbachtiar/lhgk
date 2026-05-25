<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'page_url',
        'referrer',
        'method',
        'status_code',
        'browser',
        'os',
        'device',
        'device_name',
        'country',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope untuk mendapatkan visitor hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }

    /**
     * Scope untuk mendapatkan visitor minggu ini
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('visited_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope untuk mendapatkan visitor bulan ini
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visited_at', now()->month)
                     ->whereYear('visited_at', now()->year);
    }
}
