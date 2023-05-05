<?php

namespace Fatchip\AddressValidation\extend;

use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_Parent
{
    protected function save()
    {
        $return = parent::save();

        if (Registry::getRequest()->getRequestParameter('oxid') === "fcaddressvalidation") {
            Registry::getLogger()->error("poop");
        }

        return $return;
    }
}