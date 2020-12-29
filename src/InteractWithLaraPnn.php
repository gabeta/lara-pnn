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

    public function getEligibleFields($fromConsole = false)
    {
        $this->migratePnnFromConsole = $fromConsole;

       return array_merge($this->getEligibleByType('mobile'), $this->getEligibleByType('fix'));
    }

    protected function getEligibleByType($type)
    {
        $fields = [];

        foreach ($this->pnnFields[$type] as $mobileField) {
            if($this->numberIsEligible($mobileField, $this->{$mobileField})) $fields[] = $mobileField;
        }

        return $fields;
    }

    public function numberIsEligible($key, $value)
    {
        $value = LaraPnnFacade::removeNumberSeparators($value);

        return (! isset($this->pnnDialCodeFields) && strlen($value) === 8) ||
            $this->eligibleByDialCodeField($key) || $this->eligibleByDialCode($value);
    }

    protected function eligibleByDialCode($value)
    {
        $dialCode = LaraPnnFacade::extractDialCode($value);

        return $this->verifyByDialCode($dialCode);
    }

    protected function eligibleByDialCodeField($key)
    {
        return (
                isset($this->pnnDialCodeFields) &&
                array_key_exists($key, $this->pnnDialCodeFields) &&
                $this->verifyByDialCode($this->{$this->pnnDialCodeFields[$key]})
            );
    }

    protected function verifyByDialCode($code)
    {
        return in_array($code, config('larapnn.dial_code'));
    }
}
