<?php

namespace App\Database;

/**
 * Class Track for Illuminate (DB) queries
 */
class Sessions extends AbstractModel {
    public $timestamps = true;
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $fillable = ['id','data','timestamp'];  
}
