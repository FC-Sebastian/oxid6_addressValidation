<?php

namespace Fatchip\AddressValidation\extend\Application\Controller\Admin;

use Fatchip\AddressValidation\Application\Model\Address;
use Generator;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ModuleConfiguration extends ModuleConfiguration_Parent
{
    /**
     * Default csv separator
     *
     * @var string
     */
    protected $fc_sSeparator = ";";

    /**
     * Default csv enclosure
     *
     * @var string
     */
    protected $fc_sEnclosure = '"';

    /**
     * Default csv escape
     *
     * @var string
     */
    protected $fc_sEscape = "\\";

    /**
     * Expected CSV headers
     *
     * @var string[]
     */
    protected $fc_aDefaultHeaders = ["PLZ", "City", "Country", "Country-Shortcut"];

    /**
     * Used to store array of db column names
     *
     * @var null
     */
    protected $fc_aDbColumns = null;

    /**
     * Flag to indicate file validity
     *
     * @var bool
     */
    protected $fc_blFileInvalid = false;

    /**
     * Flag to indicate csv header validity
     *
     * @var bool
     */
    protected $fc_blHeadersInvalid = false;

    /**
     * Flag to indicate success
     *
     * @var bool
     */
    protected $fc_blComplete = false;

    /**
     * Extends ModuleConfiguration save method
     * validates and imports csv
     *
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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
                $oCsvFile = fopen($aFile["tmp_name"],"r");
                $aFirstRow = fgetcsv($oCsvFile,null, $this->fcGetSeparator(), $this->fcGetEnclosure(), $this->fcGetEscape());

                if ($this->fcValidateHeaders($aFirstRow) === true) {
                    $this->fc_aDbColumns = $this->fcGetParamKeys($aFirstRow);

                    $this->fcSaveAndDelete($oCsvFile);

                    fclose($oCsvFile);
                    unlink($aFile["tmp_name"]);
                } else {
                    $this->fc_blHeadersInvalid = true;
                }
            } else {
                $this->fc_blFileInvalid = true;
            }
        }
    }

    /**
     * Generates associative arrays from CSV rows and array of db column headers
     *
     * @param object $oCsvFile
     * @return Generator
     */
    protected function  fcGetAssocCsvRow($oCsvFile)
    {
        while ($aRow = fgetcsv($oCsvFile,null, $this->fcGetSeparator(), $this->fcGetEnclosure(), $this->fcGetEscape())) {
            foreach ($aRow as $key => $value) {
                $aRow[$this->fc_aDbColumns[$key]] = utf8_encode($value);
                unset($aRow[$key]);
            }
            $aRow['OXID'] = md5($aRow['PLZ'].$aRow['CITY'].$aRow['COUNTRYSHORTCUT']);

            yield $aRow;
        }
    }

    /**
     * Uses csv headers to build and return array of db headers
     *
     * @param array $aFirstRow
     * @return array
     */
    protected function fcGetParamKeys($aFirstRow)
    {
        $aReturn = [];
        foreach ($aFirstRow as $sColumn) {
            $aReturn[] = str_replace("-","",strtoupper($sColumn));
        }
        return $aReturn;
    }

    /**
     * Loops through fcGetAssocCsvRow generator to check whether a row is present in db or needs to be inserted
     * Deletes and Inserts Addresses afterwards
     *
     * @param $oCsvFile
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function fcSaveAndDelete($oCsvFile)
    {
        $oAddress = oxNew(Address::class);
        $aAddressIds = $oAddress->fcGetIds();

        foreach($this->fcGetAssocCsvRow($oCsvFile) as $aRow) {
            $sAddressIdKey = array_search($aRow['OXID'], $aAddressIds);

            if ($sAddressIdKey === false) {
                $oAddress->fcSetInsertQueryValues($aRow);
            } else {
                unset($aAddressIds[$sAddressIdKey]);
            }
        }

        $oAddress->fcExecuteCsvInsertQuery();
        $oAddress->fcDeleteBulk($aAddressIds);

        $this->fc_blComplete = true;
    }

    /**
     * Validates given array of csv against expected csv headers
     *
     * @param $aHeaders
     * @return bool
     */
    protected function fcValidateHeaders($aHeaders)
    {
        return array_diff($aHeaders, $this->fc_aDefaultHeaders) === [];
    }

    /**
     * Loads and returns number of addresses in db
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function fcGetAddressCount()
    {
        $oAddress = oxNew(Address::class);
        return $oAddress->fcGetAddressCount();
    }

    /**
     * Returns csv separator
     *
     * @return string
     */
    public function fcGetSeparator()
    {
        return $this->fc_sSeparator;
    }

    /**
     * Returns csv enclosure
     *
     * @return string
     */
    public function fcGetEnclosure()
    {
        return $this->fc_sEnclosure;
    }

    /**
     * Returns csv escape
     *
     * @return string
     */
    public function fcGetEscape()
    {
        return $this->fc_sEscape;
    }

    /**
     * Sets csv separator
     *
     * @param string $separator
     * @return void
     */
    public function fcSetSeparator($separator)
    {
        $this->fc_sSeparator = $separator;
    }

    /**
     * Sets csv enclosure
     *
     * @param $enclosure
     * @return void
     */
    public function fcSetEnclosure($enclosure)
    {
        $this->fc_sEnclosure = $enclosure;
    }


    /**
     * Sets csv escape
     *
     * @param string $escape
     * @return void
     */
    public function fcSetEscape($escape)
    {
        $this->fc_sEscape = $escape;
    }

    /**
     * Returns file validity as bool
     *
     * @return bool
     */
    public function fcGetTypeInvalid()
    {
        return $this->fc_blFileInvalid;
    }

    /**
     * Returns csv header validity as bool
     *
     * @return bool
     */
    public function fcGetHeadersInvalid()
    {
        return $this->fc_blHeadersInvalid;
    }

    /**
     * Returns csv Import success as bool
     *
     * @return bool
     */
    public function fcGetComplete()
    {
        return $this->fc_blComplete;
    }
}