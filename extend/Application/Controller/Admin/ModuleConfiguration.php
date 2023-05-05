<?php

namespace fc\fcaddressvalidation\extend\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_Parent
{
    protected function save()
    {
        parent::save();

        if (Registry::getRequest()->getRequestParameter('oxid') === "fcaddressvalidation") {
            Registry::getLogger()->error("poop");
        }
    }
}