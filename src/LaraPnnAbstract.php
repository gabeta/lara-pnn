<?php


namespace Gabeta\LaraPnn;


interface LaraPnnAbstract
{
    public function getEligibleFields($fromConsole = false);

    public function setMigratePnnFromConsole($fromConsole = false);
}
