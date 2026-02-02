<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $connection = 'dashboard_phinnisi';
    protected $table = 'pandu_prod';
    public $timestamps = false;
    
    protected $fillable = [
        'BILLING',
        'BILLING_DATE',
        'INVOICE',
        'INVOICE_DATE',
        'NO_PKK',
        'NO_PKK_INAPORTNET',
        'PPKB_CODE',
        'VESSEL_NAME',
        'VESSEL_CODE',
        'FLAG',
        'SHIPPING_AGENT',
        'SHIPPING_TYPE',
        'REVENUE_ACCOUNT',
        'MOVEMENT',
        'LOA',
        'GRT',
        'REVENUE',
        'PILOT_ONBOARD',
        'SHIP_START_MOVING',
        'PILOT_FINISHED',
        'PILOT_OFF',
        'PILOT',
        'APPROVAL_DATE',
        'FROM_LOCATION',
        'TO_LOCATION',
        'SPK_PILOT_NUMBER',
        'NAME_BRANCH',
        'INVOICE_NUMBER',
        'DELEGATION'
    ];
    
    public function tunda()
    {
        return $this->hasOne(Tunda::class, 'BILLING', 'BILLING');
    }
}
