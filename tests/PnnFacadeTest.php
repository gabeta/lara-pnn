<?php


namespace Gabeta\LaraPnn\Tests;


use Gabeta\LaraPnn\Facades\LaraPnn;

class PnnFacadeTest extends TestCase
{
    public function test_eligible_by_format()
    {
        $this->assertTrue(LaraPnn::eligibleByFormat('88000000', 8));

        $this->assertFalse(LaraPnn::eligibleByFormat('88000000', 10));

        $this->assertFalse(LaraPnn::eligibleByFormat('88000000', 7));

        $this->assertTrue(LaraPnn::eligibleByFormat('88 00 00 00 00', 10));
    }

    public function test_extract_dial_code()
    {
        $this->assertEquals(LaraPnn::extractDialCode('00225 00 00 00 00', 8), '00225');

        $this->assertEquals(LaraPnn::extractDialCode('+2250000000000', 10), '+225');

        $this->assertNotEquals(LaraPnn::extractDialCode('+2250000000000', 8), '+225');

        $this->assertEquals(LaraPnn::extractDialCode('225 0000', 4), '225');
    }

    public function test_eligible_by_dial_code()
    {
        $this->assertTrue(LaraPnn::eligibleByDialCode('+22588000000', 8));

        $this->assertTrue(LaraPnn::eligibleByDialCode('+2258800000000', 10));

        $this->assertFalse(LaraPnn::eligibleByDialCode('+225880000000', 8));
    }

    public function test_get_dial_code()
    {
        $this->assertEquals(LaraPnn::getDialCode('00225 00 00 00 00', 8), '00225 ');

        $this->assertEquals(LaraPnn::getDialCode('+2250000000000', 10), '+225 ');

        $this->assertNotEquals(LaraPnn::getDialCode('+2250000000000', 8), '+225 ');

        $this->assertNull(LaraPnn::extractDialCode('0000', 4));
    }

    public function test_remove_number_separators()
    {
        $this->assertEquals(LaraPnn::removeNumberSeparators('00225 00 00 00 00', 8), '0022500000000');

        $this->assertEquals(LaraPnn::removeNumberSeparators('+225 00-00-00-00-00', 10), '+2250000000000');

        \Config::set('larapnn.separators', [')', '(', '-', ' ']);

        $this->assertEquals(LaraPnn::removeNumberSeparators('(+225) 00-00-00-00-00', 10), '+2250000000000');
    }

    public function test_get_number_value()
    {
        $this->assertEquals(LaraPnn::getNumberValue('00225 09 00 00 00', 8), '09000000');

        $this->assertNotEquals(LaraPnn::getNumberValue('00225 09 00 00 00', 8), '0022509000000');

        $this->assertEquals(LaraPnn::getNumberValue('+225 05-00-00-00-00', 10), '0500000000');
    }

    public function test_get_new_pnn_prefix()
    {
        $this->assertEquals(LaraPnn::getNewPnnPrefix('09000000'), '07');

        $this->assertEquals(LaraPnn::getNewPnnPrefix('03000000'), '01');

        $this->assertEquals(LaraPnn::getNewPnnPrefix('04000000'), '05');

        $this->assertEquals(LaraPnn::getNewPnnPrefix('20800000'), '21');

        $this->assertEquals(LaraPnn::getNewPnnPrefix('33000000'), '25');

        $this->assertEquals(LaraPnn::getNewPnnPrefix('23400000'), '27');

        $this->assertNotEquals(LaraPnn::getNewPnnPrefix('35000000'), '27');
    }

    public function test_formatting_pnn()
    {
        $this->assertEquals(LaraPnn::formattingPnn('0900000000'), '09 00 00 00 00');

        $this->assertEquals(LaraPnn::formattingPnn('07000000'), '07 00 00 00');

        \Config::set('larapnn.format.model_migrate', 'XX-XXXX-XXXX');
        $this->assertNotEquals(LaraPnn::formattingPnn('0900000000'), '09 00 00 00 00');

        $this->assertEquals(LaraPnn::formattingPnn('0900000000'), '09 0000 0000');

        \Config::set('larapnn.format.separator', '-');

        $this->assertNotEquals(LaraPnn::formattingPnn('0900000000'), '09 0000 0000');

        $this->assertEquals(LaraPnn::formattingPnn('0900000000'), '09-0000-0000');

        $this->assertNotEquals(LaraPnn::formattingPnn('07000000'), '07 00 00 00');

        $this->assertEquals(LaraPnn::formattingPnn('07000000'), '07-00-00-00');

        \Config::set('larapnn.format.model_rollback', 'XX-XX-XXXX');

        $this->assertNotEquals(LaraPnn::formattingPnn('07000000'), '07-00-00-00');

        $this->assertEquals(LaraPnn::formattingPnn('07000000'), '07-00-0000');
    }
}
