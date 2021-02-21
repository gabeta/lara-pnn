<?php

namespace Gabeta\LaraPnn\Console;

use Gabeta\LaraPnn\Facades\LaraPnn;

class LaraPnnMigrateCommand extends LaraPnnCommand
{
    protected $signature = 'larapnn:migrate {model} {--skip} {--take=}';

    protected $description = 'Migrate Your tel number for new plan';

    protected function changeFormat($value)
    {
        return LaraPnn::translateToNewPnnFormat($value);
    }

    protected function getEligibleFields($result)
    {
        return $result->getEligibleFieldsForMigration(true);
    }

    protected function successMessage()
    {
        return $this->model->getTable()." numbers has successful migrate";
    }
}
