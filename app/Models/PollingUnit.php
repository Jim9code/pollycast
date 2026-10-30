<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PollingUnit extends Model
{
    protected $table = 'polling_unit';
    protected $primaryKey = 'uniqueid';
    public $timestamps = false;

    protected $fillable = [
        'polling_unit_id',
        'ward_id',
        'lga_id',
        'uniquewardid',
        'polling_unit_number',
        'polling_unit_name',
        'polling_unit_description',
        'lat',
        'long',
        'entered_by_user',
        'date_entered',
        'user_ip_address',
    ];

    /**
     * Each Polling Unit belongs to an LGA (via lga_id).
     */
    public function lga()
    {
        return $this->belongsTo(Lga::class, 'lga_id', 'lga_id');
    }

    /**
     * Each Polling Unit belongs to a Ward (via uniquewardid -> ward.uniqueid).
     */
    public function ward()
    {
        return $this->belongsTo(Ward::class, 'uniquewardid', 'uniqueid');
    }

    /**
     * Each Polling Unit has many announced party results (via polling_unit_uniqueid -> polling_unit.uniqueid).
     */
    public function results()
    {
        return $this->hasMany(AnnouncedPuResult::class, 'polling_unit_uniqueid', 'uniqueid');
    }
}

