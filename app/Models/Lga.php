<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    protected $table = 'lga';
    protected $primaryKey = 'uniqueid';
    public $timestamps = false;

    protected $fillable = [
        'lga_id',
        'lga_name',
        'state_id',
        'lga_description',
        'entered_by_user',
        'date_entered',
        'user_ip_address',
    ];

    /**
     * An LGA has many Wards (matched by lga_id).
     */
    public function wards()
    {
        return $this->hasMany(Ward::class, 'lga_id', 'lga_id');
    }

    /**
     * An LGA has many Polling Units (matched by lga_id).
     */
    public function pollingUnits()
    {
        return $this->hasMany(PollingUnit::class, 'lga_id', 'lga_id');
    }
}

