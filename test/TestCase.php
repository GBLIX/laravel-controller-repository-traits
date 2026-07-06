<?php

namespace Gblix\Tests;

use Gblix\ServiceProviders\EloquentMacroServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EloquentMacroServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function setUpDatabase(): void
    {
        Schema::create('model_stubs', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }
}
