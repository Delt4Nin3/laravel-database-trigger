<?php

namespace Delt4Nin3\LaravelDatabaseTrigger\Test;

use Delt4Nin3\LaravelDatabaseTrigger\Schema\Blueprint;
use Delt4Nin3\LaravelDatabaseTrigger\Schema\Grammars\PostgresGrammar;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PostgresGrammarTest extends TestCase
{
    public function testCanCreateTrigger()
    {
        $trigger = 'after_users_delete';
        $eventObjectTable = 'users';
        $statement = function () {
            return 'DELETE FROM users WHERE id = 1;';
        };

        $blueprint = new Blueprint($trigger);
        $blueprint->create()
            ->on($eventObjectTable)
            ->statement($statement)
            ->after()
            ->delete();

        $connection = $this->getConnection();
        $statements = $blueprint->toSql($connection, $this->getGrammar());

        $actionStatement = 'create function after_users_delete() returns trigger language plpgsql as $$ begin DELETE FROM users WHERE id = 1; end; $$; create trigger after_users_delete after delete on users for each row execute function after_users_delete()';

        $this->assertEquals($actionStatement, $statements[0]);
    }

    public function testDropTrigger()
    {
        $trigger = 'before_employees_update';
        $eventObjectTable = 'employees';
        $blueprint = new Blueprint($trigger);
        $blueprint->on($eventObjectTable)->dropIfExists();

        $conn = $this->getConnection();
        $statement = $blueprint->toSql($conn, $this->getGrammar());

        $dropClause = 'drop trigger if exists before_employees_update ON employees; drop function if exists before_employees_update();';

        $this->assertEquals($dropClause, $statement[0]);
    }

    private function getConnection()
    {
        return m::mock('Illuminate\Database\Connection');
    }

    private function getGrammar()
    {
        return new PostgresGrammar;
    }
}
