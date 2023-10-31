<?php

namespace Fatchip\AddressValidation\Application\Controller;

use Fatchip\AddressValidation\Application\Model\Address;
use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\Registry;


class AddressAjaxController extends FrontendController
{

    /**
     * Validates address and echoes JSON encoded array containing status and optional parameters
     *
     * @return void
     */
    public function fcValidateAddress()
    {
        $sCountryId = Registry::getRequest()->getRequestParameter('countryId');
        $sCity = Registry::getRequest()->getRequestParameter('city');
        $sZip = Registry::getRequest()->getRequestParameter('zip');

        $oCountry = oxNew(Country::class);
        $oCountry->loadInLang(0, $sCountryId);
        $sCountryTitle = $oCountry->oxcountry__oxtitle->value;

        $oAddress = oxNew(Address::class);
        if ($oAddress->fcLoadByColumnValues(['CITY' => $sCity, 'PLZ' => $sZip, 'COUNTRY' => $sCountryTitle]) === true) {
            echo json_encode(['status' => 'valid']);
        } elseif ($oAddress->fcLoadByColumnValues(['CITY' => $sCity, 'PLZ' => $sZip]) === true) {
            echo json_encode(['status' => 'country found', 'country' => $this->fcGetCountryIdByShortcut($oAddress->fcaddresses__countryshortcut->value)]);
        } elseif ($oAddress->fcLoadByColumnValues(['PLZ' => $sZip]) === true) {
            echo json_encode(['status' => 'city found', 'city' => $oAddress->fcaddresses__city->value]);
        } else {
            echo json_encode(['status' => 'invalid']);
        }

        $this->fcKillPHP();
    }

    /**
     * Uses given ISO country code to load and return the country's oxid
     *
     * @param string $sCountryShortcut
     * @return string
     */
    protected function fcGetCountryIdByShortcut($sCountryShortcut)
    {
        $oCountry = oxNew(Country::class);
        return $oCountry->getIdByCode($sCountryShortcut);
    }

    /**
     * calls exit()
     *
     * @return void
     */
    protected function fcKillPHP()
    {
        exit();
    }
}