<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lhgk extends Model
{
    protected $connection = 'dashboard_phinnisi';
    protected $table = 'lhgk';
    
    public $timestamps = false;
    
    // Allow all columns to be mass assignable
    protected $guarded = [];
}
