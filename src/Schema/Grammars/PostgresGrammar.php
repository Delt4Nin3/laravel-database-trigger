<?php

namespace Delt4Nin3\LaravelDatabaseTrigger\Schema\Grammars;

use InvalidArgumentException;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
use Delt4Nin3\LaravelDatabaseTrigger\Schema\Blueprint;

class PostgresGrammar extends Grammar
{
    /**
     * List of table events.
     *
     * @var array
     */
    public $events = ['insert', 'update', 'delete'];

    /**
     * Possible list of trigger times.
     *
     * @var array
     */
    public $actionTimes = ['after', 'before'];

    /**
     * Compile create trigger clause.
     *
     * @param Blueprint $blueprint
     * @param Fluent $command
     * @param Connection $connection
     * @return string
     */
    public function compileCreate(Blueprint $blueprint)
    {
        return "create function {$blueprint->trigger}() returns trigger language plpgsql as $$ begin {$blueprint->statement} end; $$; create trigger {$blueprint->trigger} {$this->validateActionTiming($blueprint)} {$this->validateEvent($blueprint)} on {$blueprint->eventObjectTable} for each row execute function {$blueprint->trigger}()";
    }

    /**
     * Compile the query to determine the list of triggers.
     *
     * @return string
     */
    public function compileTriggerExists()
    {
        return 'select * from information_schema.triggers where event_object_table = ? and trigger_name = ?';
    }

    /**
     * Validate event.
     *
     * @param string $event
     * @return string
     */
    private function validateEvent(Blueprint $blueprint)
    {
        if (! in_array(strtolower($blueprint->event), $this->events)) {
            throw new InvalidArgumentException("Cannot use {$blueprint->event} as trigger event.");
        }

        return $blueprint->event;
    }

    /**
     * Validate action time.
     *
     * @param string $actionTime
     * @return string
     */
    private function validateActionTiming(Blueprint $blueprint)
    {
        if (! in_array(strtolower($blueprint->actionTiming), $this->actionTimes)) {
            throw new InvalidArgumentException("Cannot use {$blueprint->actionTiming} as trigger action timing.");
        }

        return $blueprint->actionTiming;
    }

    /**
     * Drop trigger.
     *
     * @param string $triggerTable
     * @param string $triggerName
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint)
    {
        return sprintf('drop trigger if exists %s ON %s; drop function if exists %s();', $blueprint->trigger, $blueprint->eventObjectTable, $blueprint->trigger);
    }
}
