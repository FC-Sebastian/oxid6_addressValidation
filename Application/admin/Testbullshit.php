<?php

namespace Fatchip\AddressValidation\Application\admin;

use OxidEsales\Eshop\Core\Registry;

class Testbullshit
{

    public function save()
    {
        Registry::getLogger()->error("poop");
    }
}