<?php

namespace Delt4Nin3\LaravelDatabaseTrigger\Schema;

use Closure;
use Delt4Nin3\LaravelDatabaseTrigger\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Connection;

class PostgresBuilder
{
    /**
     * Database connection.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * The schema grammar instance.
     *
     * @var \Delt4Nin3\LaravelDatabaseTrigger\Schema\Grammars\PostgresGrammar
     */
    protected $grammar;

    /**
     * Trigger name.
     *
     * @var string
     */
    protected $trigger;

    /**
     * Trigger event object table.
     *
     * @var string
     */
    protected $eventObjectTable;

    /**
     * Statements to execute for trigger.
     *
     * @var Closure
     */
    protected $callback;

    /**
     * Trigger action timing.
     *
     * @var string
     */
    protected $actionTiming;

    /**
     * Event to activate trigger.
     *
     * @var string
     */
    protected $event;

    /**
     * Create a new database Schema manager.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $this->getDefaultGrammar();
    }

    /**
     * Create new trigger.
     *
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\PostgresBuilder
     */
    public function create($trigger)
    {
        $this->trigger = $trigger;

        return $this;
    }

    /**
     * Event object table.
     *
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\PostgresBuilder
     */
    public function on($eventObjectTable)
    {
        $this->eventObjectTable = $eventObjectTable;

        return $this;
    }

    /**
     * Trigger statement.
     *
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\PostgresBuilder
     */
    public function statement(Closure $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Trigger after action timing.
     *
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\PostgresBuilder
     */
    public function after()
    {
        $this->actionTiming = ActionTiming::after();

        return $this;
    }

    /**
     * Trigger before action timing.
     *
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\PostgresBuilder
     */
    public function before()
    {
        $this->actionTiming = ActionTiming::before();

        return $this;
    }

    /**
     * Trigger insert event.
     *
     * @return void
     */
    public function insert()
    {
        $this->event = Event::insert();

        $this->callBuild();
    }

    /**
     * Trigger update event.
     *
     * @return void
     */
    public function update()
    {
        $this->event = Event::update();

        $this->callBuild();
    }

    /**
     * Trigger delete event.
     *
     * @return void
     */
    public function delete()
    {
        $this->event = Event::delete();

        $this->callBuild();
    }

    /**
     * Determine if the given trigger exists.
     *
     * @param  string  $trigger
     * @return bool
     */
    public function hasTrigger($trigger)
    {
        return count($this->connection->select(
            $this->grammar->compileTriggerExists(),
            [$trigger]
        )) > 0;
    }

    /**
     * Drop trigger.
     *
     * @return void
     */
    public function dropIfExists($trigger)
    {
        $this->build(tap($this->createBlueprint($trigger), function ($blueprint) {
            $blueprint->dropIfExists();
        }));
    }

    /**
     * Get action timing.
     *
     * @return string
     */
    protected function getActionTiming()
    {
        return $this->actionTiming;
    }

    /**
     * Get event.
     *
     * @return string
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * Get trigger event object table.
     *
     * @return string
     */
    protected function getEventObjectTable()
    {
        return $this->eventObjectTable;
    }

    /**
     * Get trigger statement.
     *
     * @return Closure
     */
    protected function getStatement()
    {
        return $this->callback;
    }

    /**
     * Call build to execute blueprint to build trigger.
     *
     * @return void
     */
    public function callBuild()
    {
        $eventObjectTable = $this->getEventObjectTable();
        $callback = $this->getStatement();
        $actionTiming = $this->getActionTiming();
        $event = $this->getEvent();

        $this->build(tap(
            $this->createBlueprint($this->trigger),
            function (Blueprint $blueprint) use ($eventObjectTable, $callback, $actionTiming, $event) {
                $blueprint->create();
                $blueprint->on($eventObjectTable);
                $blueprint->statement($callback);
                $blueprint->$actionTiming();
                $blueprint->$event();
            }
        ));
    }

    /**
     * Execute the blueprint to build trigger.
     *
     * @param  \Delt4Nin3\LaravelDatabaseTriggers\Schema\Blueprint $blueprint
     * @return void
     */
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param string $trigger
     * @param string $eventTable
     * @param Closure $callback
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\Blueprint
     */
    protected function createBlueprint($trigger)
    {
        return new Blueprint($trigger);
    }

    /**
     * Get default schema grammar instance.
     *
     * @return \Delt4Nin3\LaravelDatabaseTrigger\Schema\Grammars\PostgresGrammar
     */
    protected function getDefaultGrammar()
    {
        return new PostgresGrammar;
    }
}
