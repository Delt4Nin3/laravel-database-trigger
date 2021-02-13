<?php

namespace Delt4Nin3\LaravelDatabaseTrigger\Test;

use Delt4Nin3\LaravelDatabaseTrigger\Schema\PostgresBuilder as Builder;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PostgresBuilderTest extends TestCase
{
    public function testTriggerCorrectlyCallsGrammar()
    {
        $connection = m::mock('Illuminate\Database\Connection');
        $grammar = m::mock('stdClass');
        $connection->shouldReceive('getSchemaGrammar')->andReturn($grammar);
        $builder = new Builder($connection);
        $connection->shouldReceive('select')->once()->andReturn(['trigger']);

        $this->assertTrue($builder->hasTrigger('trigger'));
    }
}
