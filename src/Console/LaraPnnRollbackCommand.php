<?php


namespace Gabeta\LaraPnn\Console;


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
        // TODO: Implement changeFormat() method.
    }

    protected function successMessage()
    {
        return $this->model->getTable()." has successful rollback";
    }
}
