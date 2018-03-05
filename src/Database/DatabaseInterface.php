<?php

namespace ByJG\DbMigration\Database;

use Psr\Http\Message\UriInterface;

interface DatabaseInterface
{
    public static function prepareEnvironment(UriInterface $dbDriver);

    public function createDatabase();
    
    public function dropDatabase();
    
    public function getVersion();

    public function updateVersionTable();
    
    public function executeSql($sql);
    
    public function setVersion($version, $status);
    
    public function createVersion();
}
