<?php

namespace Delt4Nin3\LaravelDatabaseTrigger;

use Delt4Nin3\LaravelDatabaseTrigger\Command\TriggerMakeCommand;
use Delt4Nin3\LaravelDatabaseTrigger\Schema\MySqlBuilder;
use Illuminate\Support\ServiceProvider;

class TriggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TriggerMakeCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->singleton('trigger-builder', function () {
            return new MySqlBuilder(app('db.connection'));
        });
    }
}
