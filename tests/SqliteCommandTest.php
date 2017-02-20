<?php
/**
 * User: jg
 * Date: 19/02/17
 * Time: 19:52
 */


require_once 'BaseCommand.php';


class SqliteCommandTest extends BaseCommand
{
    protected $uri = 'sqlite:///tmp/teste.sqlite';

    /**
     * @var \ByJG\DbMigration\Migration
     */
    protected $migrate = null;

    public function setUp()
    {
        $this->migrate = new \ByJG\DbMigration\Migration(new \ByJG\Util\Uri($this->uri), __DIR__ . '/../example/sqlite');
        parent::setUp();
    }
}
