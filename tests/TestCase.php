<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function setUp(): void
    {
        parent::setUp();

        // Keep chatbot DB reads on the in-memory test connection to avoid
        // network timeouts when a non-test read replica is configured.
        config(['chatbot.read_connection' => config('database.default')]);
        
        // Enable foreign key constraints for SQLite testing
        if (config('database.default') === 'sqlite') {
            $connection = $this->app['db']->connection();
            $connection->getPdo()->exec('PRAGMA foreign_keys = ON;');
        }

        // Some branches omit the translations migration file while still
        // relying on TranslationSeeder/model calls in integration tests.
        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('key');
                $table->string('value');
                $table->timestamps();
            });
        }
    }
}