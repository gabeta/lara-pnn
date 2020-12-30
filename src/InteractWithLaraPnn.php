<?php

namespace Gabeta\LaraPnn;

use Gabeta\LaraPnn\Facades\LaraPnn as LaraPnnFacade;

trait InteractWithLaraPnn
{
    protected $migratePnnFromConsole = false;

    public function getAttribute($key)
    {
        $attribute = parent::getAttribute($key);

        if ((in_array($key, $this->pnnFields['mobile']) || in_array($key, $this->pnnFields['fix']))
            && $this->numberIsEligible($key, $attribute) && ! $this->migratePnnFromConsole) {
           return LaraPnnFacade::translateToNewPnnFormat($attribute);
        }

        return $attribute;
    }

    public function setMigratePnnFromConsole($fromConsole = false)
    {
        $this->migratePnnFromConsole = $fromConsole;
    }

    public function getEligibleFieldsForMigration($fromConsole = false)
    {
        return $this->getEligibleFields($fromConsole);
    }

    public function getEligibleFieldsForRollBack($fromConsole = false)
    {
        return $this->getEligibleFields($fromConsole, 10);
    }

    public function getEligibleFields($fromConsole = false, $digits = 8)
    {
        $this->migratePnnFromConsole = $fromConsole;

        return array_merge($this->getEligibleByType('mobile', $digits), $this->getEligibleByType('fix', $digits));
    }

    protected function getEligibleByType($type, $digits = 8)
    {
        $fields = [];

        foreach ($this->pnnFields[$type] as $mobileField) {
            $isEligible = $this->numberIsEligible($mobileField, $this->{$mobileField}, $digits);

            if($isEligible) $fields[] = $mobileField;
        }

        return $fields;
    }

    public function numberIsEligible($key, $value, $digits = 8)
    {
        return (
                (!isset($this->pnnDialCodeFields["{$key}"]))
                || ($this->eligibleByDialCodeField($key)
                || LaraPnnFacade::eligibleByDialCode($value, $digits))
            ) && LaraPnnFacade::eligibleByFormat($value, $digits);
    }

    protected function eligibleByDialCodeField($key)
    {
        return (
                isset($this->pnnDialCodeFields) &&
                array_key_exists($key, $this->pnnDialCodeFields) &&
                LaraPnnFacade::verifyByDialCode($this->{$this->pnnDialCodeFields[$key]})
            );
    }
}
