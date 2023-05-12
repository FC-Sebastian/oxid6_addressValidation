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

    protected $_blComplete = false;

    //2:43

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
                    $aParamKeys = $this->fcGetParamKeys($afirstrow);

                    $iStart = time();
                    $this->fcSaveAndDelete($aParamKeys, $file, $aFormData);
                    Registry::getLogger()->error('total time: '.date('i:s', time() - $iStart));

                    fclose($file);
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
            $aReturn[] = str_replace("-","",strtoupper($column));
        }
        return $aReturn;
    }

    protected function fcSaveAndDelete($aParamKeys, $file, $aFormData)
    {
        $oAddress = oxNew(Address::class);
        $aDeleteIds = $oAddress->getIds();
        $aSaveParams = [];

        while($aRow = fgetcsv($file,null, $aFormData["csv_separator"], $aFormData["csv_enclosure"], $aFormData["csv_escape"])) {
            $aParams = $this->fcGetParams($aParamKeys, $aRow);
            $oAddress->loadByColumnValues($aParams);

            if ($oAddress->loadByColumnValues($aParams) === false) {
                $aSaveParams[] = $aParams;
            } else {
                unset($aDeleteIds[array_search($oAddress->getId(),$aDeleteIds)]);
            }
        }
        $this->fcDeleteAddresses($aDeleteIds);
        $this->fcSaveAddresses($aSaveParams);

        $this->_blComplete = true;
    }

    protected function fcDeleteAddresses($aDeleteIds)
    {
        if (!empty($aDeleteIds)) {
            $oAddress = oxNew(Address::class);
            foreach ($aDeleteIds as $deleteId) {
                $oAddress->delete($deleteId);
            }
        }
    }

    protected function fcSaveAddresses($aSaveParams)
    {
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

    public function fcGetAddressCount()
    {
        $oAddress = oxNew(Address::class);
        return $oAddress->getAddressCount();
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

    public function fcGetTypeInvalid()
    {
        return $this->_blFileInvalid;
    }

    public function fcGetHeadersInvalid()
    {
        return $this->_blHeadersInvalid;
    }

    public function fcGetComplete()
    {
        return $this->_blComplete;
    }
}