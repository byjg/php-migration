<?php

namespace ByJG\DbMigration\Commands;


interface CommandInterface
{
    public function createDatabase();
    
    public function dropDatabase();
    
    public function getVersion();
    
    public function setVersion($version);
    
    public function createVersion();
}