<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncedLgaResult extends Model
{
    protected $table = 'announced_lga_results';
    protected $primaryKey = 'result_id';
    public $timestamps = false;

    protected $fillable = [
        'lga_name',
        'party_abbreviation',
        'party_score',
        'entered_by_user',
        'date_entered',
        'user_ip_address',
    ];
}

