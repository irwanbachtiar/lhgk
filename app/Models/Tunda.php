<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tunda extends Model
{
    protected $connection = 'dashboard_phinnisi';
    protected $table = 'tunda_prod';
    public $timestamps = false;
    
    protected $fillable = [
        'BILLING',
        'REVENUE',
        'namatunda'
    ];
}
