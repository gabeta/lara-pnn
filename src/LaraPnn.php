<?php

namespace Gabeta\LaraPnn;

use Gabeta\GsmDetector\GsmDetector;

trait LaraPnn
{
    protected $gsmDetector = null;

    public function getAttribute($key)
    {
        $attribute = parent::getAttribute($key);

        if (array_key_exists($key, $this->pnnFields) && $this->numberIsEligible($key, $attribute)) {
           return $this->translateToNewPnnFormat($attribute);
        }

        return $attribute;
    }

    public function translateToNewPnnFormat($value)
    {
        $subValue = $this->getNumberValue($value);

        $prefix = $this->getNewPnnPrefix($subValue);

        return $this->getDialCode($value).$this->formattingPnn($prefix, $subValue);
    }

    protected function formattingPnn($prefix, $value)
    {
        $value = $prefix.$value;

        $format = config('larapnn.format.model');

        $formatPieces = explode('-', $format);

        [$pattern, $replacement] = $this->generateFormatPattern($formatPieces);

        return preg_replace('#'.$pattern.'#', $replacement, $value);
    }

    protected function generateFormatPattern($pieces)
    {
        $pattern = '';
        $replacement = '';

        $i = 1;
        foreach ($pieces as $piece) {
            $pattern .= '(\d{'.strlen($piece).'})';
            $replacement .= '$'.$i;
            if ($i !== count($pieces)) $replacement .= config('larapnn.format.separator');
            $i++;
        }

        return [$pattern, $replacement];
    }

    protected function getDialCode($value)
    {
        return (is_null($this->extractDialCode($value)) ? '' : $this->extractDialCode($value).' ');
    }

    protected function getNumberValue($value)
    {
        $value = $this->removeNumberSeparators($value);

        return substr($value, -8);
    }

    protected function removeNumberSeparators($value)
    {
        $separators = config('larapnn.separators');

        foreach ($separators as $separator) {
            $value = str_replace($separator, '', $value);
        }

        return $value;
    }

    public function numberIsEligible($key, $value)
    {
        $value = $this->removeNumberSeparators($value);

        return (! isset($this->pnnDialCodeFields) && strlen($value) === 8) ||
            $this->eligibleByDialCodeField($key) || $this->eligibleByDialCode($value);
    }

    protected function extractDialCode($value)
    {
        $value = $this->removeNumberSeparators($value);

        $length = strlen($value);

        if ($length === 11) {
            return substr($value, 0, 3);
        }

        if ($length === 12) {
            return substr($value, 0, 4);
        }

        if ($length === 13) {
            return substr($value, 0, 5);
        }

        return null;
    }

    protected function eligibleByDialCode($value)
    {
        $dialCode = $this->extractDialCode($value);

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

    protected function configGsmDetector()
    {
        if (is_null($this->gsmDetector)) {
            $this->gsmDetector = new GsmDetector($this->getConfigArray());
        }

        return $this->gsmDetector;
    }

    protected function getConfigArray()
    {
        $gsmConfig = [];

        $config = $this->getGsmConfig();

        foreach ($config as $key => $value) {
            $gsmConfig[$key] = [
                'fix' => $value['fix'],
                'mobile' => $value['mobile']
            ];
        }

        return $gsmConfig;
    }

    protected function getNewPnnPrefix($value)
    {
        $config = $this->getGsmConfig();

        $gsmName = $this->configGsmDetector()->getGsmName($value);

        return $this->getPnnTelDigits($config[$gsmName], $value);
    }

    protected function getPnnTelDigits($gsm, $value)
    {
        $gsmDetector = $this->configGsmDetector();

        if ($gsmDetector->isType($value, 'fix')) {
            return $gsm['fix_digit'];
        }

        if ($gsmDetector->isType($value, 'mobile')) {
            return $gsm['mobile_digit'];
        }

        return null;
    }

    protected function getGsmConfig()
    {
        return config('larapnn.gsm');
    }
}
