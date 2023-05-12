<?php

namespace Fatchip\AddressValidation\Application\Controller;

use Fatchip\AddressValidation\Application\Model\Address;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\Registry;

class AddressAjaxController extends FrontendController
{

    public function validateAddress()
    {
        $sCountryId = Registry::getRequest()->getRequestParameter('countryId');
        $sCity = Registry::getRequest()->getRequestParameter('city');
        $sZip = Registry::getRequest()->getRequestParameter('zip');

        $oCountry = oxNew(Country::class);
        $sCountryTitle = $oCountry->load($sCountryId)->oxcountry__oxtitle->value;

        $oAddress = oxNew(Address::class);
        if ($oAddress->loadByColumnValues(['CITY' => $sCity, 'PLZ' => $sZip, 'COUNTRY' => $sCountryTitle]) === true) {
            echo json_encode(['status' => 'valid']);
        } elseif ($oAddress->loadByColumnValues(['CITY' => $sCity, 'PLZ' => $sZip]) === true) {
            echo json_encode(['status' => 'country found', 'country' => $oAddress->fcaddresses__country->value]);
        } elseif ($oAddress->loadByColumnValues(['PLZ' => $sZip]) === true) {
            echo json_encode(['status' => 'city found', 'city' => $oAddress->fcaddresses__city->value]);
        } else {
            echo json_encode(['status' => 'invalid']);
        }

        exit();
    }
}