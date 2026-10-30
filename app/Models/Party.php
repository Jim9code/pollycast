<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $table = 'party';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'partyid',
        'partyname',
    ];

    /**
     * Helper to normalize party abbreviation to 4 chars (e.g. 'LABOUR' -> 'LABO')
     * so it matches announced_pu_results.party_abbreviation (CHAR(4)).
     */
    public function getAbbreviationAttribute(): string
    {
        return substr(trim($this->partyid), 0, 4);
    }
}

