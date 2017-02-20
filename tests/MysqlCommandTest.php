<?php
/**
 * User: jg
 * Date: 19/02/17
 * Time: 19:52
 */


require_once 'BaseCommand.php';


class MysqlCommandTest extends BaseCommand
{
    protected $uri = 'mysql://root:password@mysql-container/migratedatabase';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/mysql');
        parent::setUp();
    }
}
