<?php

namespace fc\fcaddressvalidation\Application\admin;

use OxidEsales\Eshop\Core\Registry;

class Testbullshit
{

    public function save()
    {
        Registry::getLogger()->error("poop");
    }
}