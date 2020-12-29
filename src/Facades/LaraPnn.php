<?php


namespace Gabeta\LaraPnn\Facades;


use Illuminate\Support\Facades\Facade;

class LaraPnn extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laraPnn';
    }
}
