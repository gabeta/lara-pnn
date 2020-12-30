<?php


namespace Gabeta\LaraPnn\Console;


use Gabeta\LaraPnn\Facades\LaraPnn;

class LaraPnnRollbackCommand extends LaraPnnCommand
{
    protected $signature = 'larapnn:rollback {model}';

    protected $description = 'Rollback Your tel number for last plan';

    protected function getEligibleFields($result)
    {
        return $result->getEligibleFieldsForRollBack(true);
    }

    protected function changeFormat($value)
    {
        return LaraPnn::rollbackToOldFormat($value);
    }

    protected function successMessage()
    {
        return $this->model->getTable()." numbers has successful rollback";
    }
}
