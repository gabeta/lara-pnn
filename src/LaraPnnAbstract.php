<?php


namespace Gabeta\LaraPnn;


interface LaraPnnAbstract
{
    public function translateToNewPnnFormat($value);

    public function numberIsEligible($value);
}
