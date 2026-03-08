<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable foreign key constraints for SQLite testing
        if (config('database.default') === 'sqlite') {
            $connection = $this->app['db']->connection();
            $connection->getPdo()->exec('PRAGMA foreign_keys = ON;');
        }
    }
}