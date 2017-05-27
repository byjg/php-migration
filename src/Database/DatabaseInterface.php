<?php

namespace ByJG\DbMigration\Database;

use ByJG\Util\Uri;

interface DatabaseInterface
{
    public static function prepareEnvironment(Uri $dbDriver);

    public function createDatabase();
    
    public function dropDatabase();
    
    public function getVersion();

    public function updateVersionTable();
    
    public function executeSql($sql);
    
    public function setVersion($version, $status);
    
    public function createVersion();
}
