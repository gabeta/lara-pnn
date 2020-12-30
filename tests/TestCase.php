<?php
namespace Gabeta\LaraPnn\Tests;

use \Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            "Gabeta\LaraPnn\laraPnnServiceProvider"
        ];
    }
}
