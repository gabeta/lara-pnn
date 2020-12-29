<?php


namespace Gabeta\LaraPnn;


interface LaraPnnAbstract
{
    public function getEligibleFields($fromConsole = false, $digits = 8);

    public function getEligibleFieldsForMigration($fromConsole = false);

    public function getEligibleFieldsForRollBack($fromConsole = false);

    public function setMigratePnnFromConsole($fromConsole = false);
}
