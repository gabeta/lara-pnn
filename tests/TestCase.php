<?php
namespace Gabeta\LaraPnn\Tests;

use Gabeta\LaraPnn\laraPnnServiceProvider;
use \Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            laraPnnServiceProvider::class,
        ];
    }
}
