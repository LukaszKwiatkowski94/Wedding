<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_unconfigured_application_redirects_to_setup(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/setup');
    }

    public function test_database_is_isolated_in_memory(): void
    {
        $connection = $this->app['db']->connection();

        $this->assertSame('sqlite', $connection->getDriverName());
        $this->assertSame(':memory:', $connection->getDatabaseName());
        $this->assertSame('sqlite', $connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));
    }
}
