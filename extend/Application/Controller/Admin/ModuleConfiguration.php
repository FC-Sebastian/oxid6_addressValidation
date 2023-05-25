<?php

namespace Fatchip\AddressValidation\extend\Application\Controller\Admin;

use Fatchip\AddressValidation\Application\Model\Address;
use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_Parent
{
    protected $_sSeparator = ";";

    protected $_sEnclosure = '"';

    protected $_sEscape = "\\";

    protected $_aDefaultHeaders = ["PLZ", "City", "Country", "Country-Shortcut"];

    protected $_aDbColumns = null;

    protected $_aAddressIds = null;

    protected $_blFileInvalid = false;

    protected $_blHeadersInvalid = false;

    protected $_blComplete = false;

    public function save()
    {
        parent::save();

        if (Registry::getRequest()->getRequestParameter('oxid') === "fcaddressvalidation") {
            $aFormData = Registry::getRequest()->getRequestParameter("fc");
            $aFile = Registry::getConfig()->getUploadedFile("fc_csvFile");

            $this->fcSetEnclosure($aFormData['csv_enclosure']);
            $this->fcSetEscape($aFormData['csv_escape']);
            $this->fcSetSeparator($aFormData['csv_separator']);

            if ($aFile["type"] === "text/csv") {
                $file = fopen($aFile["tmp_name"],"r");

                $aFirstRow = fgetcsv($file,null, $this->fcGetSeparator(), $this->fcGetEnclosure(), $this->fcGetEscape());

                if ($this->fcValidateHeaders($aFirstRow) === true) {
                    $this->_aDbColumns = $this->fcGetParamKeys($aFirstRow);

                    $this->fcSaveAndDelete($file);

                    fclose($file);
                    unlink($aFile["tmp_name"]);
                } else {
                    $this->_blHeadersInvalid = true;
                }
            } else {
                $this->_blFileInvalid = true;
            }
        }
    }

    protected function  fcGetAssocCsvRow($file)
    {

        while ($aRow = fgetcsv($file,null, $this->fcGetSeparator(), $this->fcGetEnclosure(), $this->fcGetEscape())) {
            foreach ($aRow as $key => $value) {
                $aRow[$this->_aDbColumns[$key]] = utf8_encode($value);
                unset($aRow[$key]);
            }
            $aRow['OXID'] = md5($aRow['PLZ'].$aRow['CITY'].$aRow['COUNTRYSHORTCUT']);

            yield $aRow;
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

    protected function fcSaveAndDelete($file)
    {
        $oAddress = oxNew(Address::class);
        $aAddressIds = $oAddress->getIds();

        foreach($this->fcGetAssocCsvRow($file) as $aRow) {
            $sAddressIdKey = array_search($aRow['OXID'], $aAddressIds);

            if ($sAddressIdKey === false) {
                $oAddress->setInsertQueryValues($aRow);
            } else {
                unset($aAddressIds[$sAddressIdKey]);
            }
        }

        $oAddress->executeCsvInsertQuery();
        $oAddress->deleteBulk($aAddressIds);

        $this->_blComplete = true;
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

    public function fcSetSeparator($separator)
    {
        $this->_sSeparator = $separator;
    }

    public function fcSetEnclosure($enclosure)
    {
        $this->_sEnclosure = $enclosure;
    }

    public function fcSetEscape($escape)
    {
        $this->_sEscape = $escape;
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