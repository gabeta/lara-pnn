<?php


namespace Gabeta\LaraPnn;

use Gabeta\GsmDetector\GsmDetector;

class LaraPnn
{
    protected $gsmDetector = null;

    /**
     * @param $value
     * @param int $digits
     * @return bool
     */
    public function eligibleByDialCode($value, $digits = 8)
    {
        $dialCode = $this->extractDialCode($value, $digits);

        return $this->verifyByDialCode($dialCode);
    }

    public function translateToNewPnnFormat($value)
    {
        $subValue = $this->getNumberValue($value);

        $prefix = $this->getNewPnnPrefix($subValue);

        return $this->getDialCode($value).$this->formattingPnn("{$prefix} {$subValue}");
    }

    public function rollbackToOldFormat($value)
    {
        $subValue = $this->getNumberValue($value, 10);

        return $this->getDialCode($value, 10).$this->formattingPnn(substr($subValue,  2));
    }

    /**
     * @param $value
     * @return string|string[]|null
     */
    public function formattingPnn($value)
    {
        $format = strlen($value) === 10 ? config('larapnn.format.model_migrate') : config('larapnn.format.model_rollback');

        $formatPieces = explode('-', $format);

        [$pattern, $replacement] = $this->generateFormatPattern($formatPieces);

        return preg_replace('#'.$pattern.'#', $replacement, $value);
    }

    /**
     * @param $pieces
     * @return array
     */
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

    /**
     * @param $value
     * @param int $digits
     * @return string
     */
    public function getDialCode($value, $digits = 8)
    {
        return (is_null($this->extractDialCode($value, $digits)) ? '' : $this->extractDialCode($value, $digits).' ');
    }

    /**
     * @param $value
     * @param int $digits
     * @return false|string|null
     */
    public function extractDialCode($value, $digits = 8)
    {
        $value = $this->removeNumberSeparators($value);

        $length = strlen($value);

        if ($length === ($digits + 3)) {
            return substr($value, 0, 3);
        }

        if ($length === ($digits + 4)) {
            return substr($value, 0, 4);
        }

        if ($length === ($digits + 5)) {
            return substr($value, 0, 5);
        }

        return null;
    }

    /**
     * @param $code
     * @return bool
     */
    public function verifyByDialCode($code)
    {
        return in_array($code, config('larapnn.dial_code'));
    }

    /**
     * @param $value
     * @param int $digits
     * @return false|string
     */
    public function getNumberValue($value, $digits = 8)
    {
        $value = $this->removeNumberSeparators($value);

        return substr($value, -($digits));
    }

    /**
     * @param $value
     * @param int $digits
     * @return bool
     */
    public function eligibleByFormat($value, $digits = 8)
    {
        $value = $this->removeNumberSeparators($value);

        $dialCode = $this->extractDialCode($value, $digits);

        return (!is_null($dialCode) && $this->verifyByDialCode($dialCode)) || strlen($value) === $digits;
    }

    /**
     * @param $value
     * @return string|string[]
     */
    public function removeNumberSeparators($value)
    {
        $separators = config('larapnn.separators');

        foreach ($separators as $separator) {
            $value = str_replace($separator, '', $value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return mixed|string|null
     * @throws \Gabeta\GsmDetector\Exceptions\GsmDetectorException
     */
    public function getNewPnnPrefix($value)
    {
        $config = $this->getGsmConfig();

        $gsmName = $this->configGsmDetector()->getGsmName($value);

        return is_null($gsmName) ? '' : $this->getPnnTelDigits($config[$gsmName], $value);
    }

    /**
     * @param $gsm
     * @param $value
     * @return mixed|null
     * @throws \Gabeta\GsmDetector\Exceptions\GsmDetectorException
     */
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

    /**
     * @return GsmDetector|null
     * @throws \Gabeta\GsmDetector\Exceptions\GsmDetectorException
     */
    protected function configGsmDetector()
    {
        if (is_null($this->gsmDetector)) {
            $this->gsmDetector = new GsmDetector($this->getConfigArray());
        }

        return $this->gsmDetector;
    }

    /**
     * @return array
     */
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

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getGsmConfig()
    {
        return config('larapnn.gsm');
    }
}
