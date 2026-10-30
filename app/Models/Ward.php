<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'ward';
    protected $primaryKey = 'uniqueid';
    public $timestamps = false;

    protected $fillable = [
        'ward_id',
        'ward_name',
        'lga_id',
        'ward_description',
        'entered_by_user',
        'date_entered',
        'user_ip_address',
    ];

    /**
     * Each Ward belongs to an LGA (matched by lga_id).
     */
    public function lga()
    {
        return $this->belongsTo(Lga::class, 'lga_id', 'lga_id');
    }

    /**
     * A Ward has many Polling Units (matched via uniquewardid -> uniqueid).
     */
    public function pollingUnits()
    {
        return $this->hasMany(PollingUnit::class, 'uniquewardid', 'uniqueid');
    }
}

