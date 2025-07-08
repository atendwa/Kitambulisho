<?php

namespace Atendwa\Kitambulisho\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Atendwa\Kitambulisho\KitambulishoServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            KitambulishoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
    }
}
