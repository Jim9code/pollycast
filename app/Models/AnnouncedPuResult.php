<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncedPuResult extends Model
{
    protected $table = 'announced_pu_results';
    protected $primaryKey = 'result_id';
    public $timestamps = false;

    protected $fillable = [
        'polling_unit_uniqueid',
        'party_abbreviation',
        'party_score',
        'entered_by_user',
        'date_entered',
        'user_ip_address',
    ];

    /**
     * Each announced result row belongs to a Polling Unit (via polling_unit_uniqueid -> polling_unit.uniqueid).
     */
    public function pollingUnit()
    {
        return $this->belongsTo(PollingUnit::class, 'polling_unit_uniqueid', 'uniqueid');
    }
}

