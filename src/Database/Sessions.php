<?php

namespace Pitch7900\Database;

use Exception;

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

    public static function exists()
    {
        if (!Sessions::hasTable(Sessions::$table)) {
            $sqlscript = __DIR__."/../../documents/scripts.sql";
            if (file_exists($sqlscript)){
                $file = fopen($sqlscript, "r");
                fclose($sqlscript);
                throw new Exception("Table :".Sessions::$table." is missing !\nPlease create using the following script: \n".$file);
            } else {
                throw new Exception("Table :".Sessions::$table." is missing !\n");
            }
        }
    }
}
