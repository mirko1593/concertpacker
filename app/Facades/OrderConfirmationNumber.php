<?php 

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\RandomOrderConfirmationNumberGenerator;

class OrderConfirmationNumber extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RandomOrderConfirmationNumberGenerator::class;
    }
}