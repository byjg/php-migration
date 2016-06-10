<?php

namespace ByJG\DbMigration\Commands;


use ByJG\AnyDataset\ConnectionManagement;

interface CommandInterface
{
    public static function prepareEnvironment(ConnectionManagement $connection);

    public function createDatabase();
    
    public function dropDatabase();
    
    public function getVersion();
    
    public function executeSql($sql);
    
    public function setVersion($version);
    
    public function createVersion();
}
