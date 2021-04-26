<?php

namespace Actengage\LaravelPassendo\Tests;

use Actengage\LaravelPassendo\PassendoServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            PassendoServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('services.messagegears', [
            'api_key' => 'API_KEY',
            'account_id' => 'ACCOUNT_ID',
            'campaign_id' => 'CAMPAIGN_ID'
        ]);
    }
}