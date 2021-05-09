<?php

namespace Pitch7900\Database;


/**
 * Class Track for Illuminate (DB) queries
 * Need to create a database with illuminate
 */
class Sessions extends AbstractModel
{
    public $timestamps = true;
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'data', 'timestamp'];
}
