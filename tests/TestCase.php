<?php
namespace Gabeta\LaraPnn\Tests;

use Gabeta\LaraPnn\laraPnnServiceProvider;
use \Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            laraPnnServiceProvider::class,
        ];
    }
}
