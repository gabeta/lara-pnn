<?php


namespace Gabeta\LaraPnn;

use Gabeta\GsmDetector\GsmDetector;

class LaraPnn
{
    protected $gsmDetector = null;

    public function translateToNewPnnFormat($value)
    {
        $subValue = $this->getNumberValue($value);

        $prefix = $this->getNewPnnPrefix($subValue);

        return $this->getDialCode($value).$this->formattingPnn($prefix, $subValue);
    }

    public function formattingPnn($prefix, $value)
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

    public function getDialCode($value)
    {
        return (is_null($this->extractDialCode($value)) ? '' : $this->extractDialCode($value).' ');
    }

    public function extractDialCode($value)
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

    public function getNumberValue($value)
    {
        $value = $this->removeNumberSeparators($value);

        return substr($value, -8);
    }

    public function removeNumberSeparators($value)
    {
        $separators = config('larapnn.separators');

        foreach ($separators as $separator) {
            $value = str_replace($separator, '', $value);
        }

        return $value;
    }

    public function getNewPnnPrefix($value)
    {
        $config = $this->getGsmConfig();

        $gsmName = $this->configGsmDetector()->getGsmName($value);

        return $this->getPnnTelDigits($config[$gsmName], $value);
    }

    public function getPnnTelDigits($gsm, $value)
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

    protected function getGsmConfig()
    {
        return config('larapnn.gsm');
    }
}
