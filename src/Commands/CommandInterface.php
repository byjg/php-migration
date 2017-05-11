<?php

namespace ByJG\DbMigration\Commands;

use ByJG\Util\Uri;

interface CommandInterface
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
