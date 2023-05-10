<?php

namespace Fatchip\AddressValidation\extend\Application\Controller\Admin;

use Fatchip\AddressValidation\Application\Model\Address;
use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_Parent
{
    protected $_sSeparator = ",";

    protected $_sEnclosure = '"';

    protected $_sEscape = "\\";

    protected $_aDefaultHeaders = ["PLZ", "City", "Country", "Country-Shortcut"];

    protected $_blFileInvalid = false;

    protected $_blHeadersInvalid = false;

    //3:41

    /**
     * @throws \Exception
     */
    public function save()
    {
        parent::save();

        if (Registry::getRequest()->getRequestParameter('oxid') === "fcaddressvalidation") {
            $aFormData = Registry::getRequest()->getRequestParameter("fc");
            $aFile = Registry::getConfig()->getUploadedFile("fc_csvFile");

            if ($aFile["type"] === "text/csv") {
                $file = fopen($aFile["tmp_name"],"r");
                $afirstrow = fgetcsv($file,null, $aFormData["csv_separator"], $aFormData["csv_enclosure"], $aFormData["csv_escape"]);

                if ($this->fcValidateHeaders($afirstrow) === true) {
                    $oAddress = oxNew(Address::class);
                    $aDeleteIds = $oAddress->getIds();
                    $aParamKeys = $this->fcGetParamKeys($afirstrow);
                    $aSaveParams = [];
                    $iStart = time();

                    while($aRow = fgetcsv($file,null, $aFormData["csv_separator"], $aFormData["csv_enclosure"], $aFormData["csv_escape"])) {
                        $aParams = $this->fcGetParams($aParamKeys, $aRow);
                        $aAddressId = $oAddress->loadIdByParams($aParams);

                        if (empty($aAddressId)) {
                            $aSaveParams[] = $aParams;
                        } else {
                            unset($aDeleteIds[array_search($aAddressId['OXID'],$aDeleteIds)]);
                        }
                    }
                    $this->fcSaveAndDelete($aDeleteIds, $aSaveParams);
                    Registry::getLogger()->error('total time: '.date('i:s', time() - $iStart));
                } else {
                    $this->_blHeadersInvalid = true;
                }
            } else {
                $this->_blFileInvalid = true;
            }
        }
    }

    protected function fcGetParamKeys($aFirstRow)
    {
        $aReturn = [];
        foreach ($aFirstRow as $column) {
            $aReturn[] = "fcaddresses__".str_replace("-","",strtolower($column));
        }
        return $aReturn;
    }

    protected function fcSaveAndDelete($aDeleteIds, $aSaveParams)
    {
        if (!empty($aDeleteIds)) {
            $oAddress = oxNew(Address::class);
            foreach ($aDeleteIds as $deleteId) {
                $oAddress->delete($deleteId);
            }
        }
        if (!empty($aSaveParams)) {
            foreach ($aSaveParams as $aParams) {
                $oAddress = oxNew(Address::class);
                $oAddress->assign($aParams);
                $oAddress->save();
            }
        }
    }

    protected function fcGetParams($aHeaders, $aRow)
    {
        $aParams = [];
        for($i = 0; $i < count($aHeaders); $i++) {
            $aParams[$aHeaders[$i]] = utf8_encode($aRow[$i]);
        }
        return $aParams;
    }

    protected function fcValidateHeaders($aHeaders)
    {
        return array_diff($aHeaders, $this->_aDefaultHeaders) === [];
    }

    public function fcGetSeparator()
    {
        return $this->_sSeparator;
    }

    public function fcGetEnclosure()
    {
        return $this->_sEnclosure;
    }

    public function fcGetEscape()
    {
        return $this->_sEscape;
    }
}