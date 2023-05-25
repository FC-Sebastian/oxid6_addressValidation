<?php

namespace Fatchip\AddressValidation\Application\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class Address extends MultiLanguageModel
{
    protected static $_aRootBanner = [];

    protected ?string $_sCsvInsertQuery = null;

    /**
     * @var string Name of current class
     */
    protected $_sClassName = 'fcaddresses';

    public function __construct()
    {
        parent::__construct();
        $this->init("fcaddresses");
    }

    public function loadIdByAddress($sZip, $sCity, $sCountryShortcut)
    {
        $db = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $db->select("OXID FROM fcaddresses WHERE PLZ = '{$sZip}' AND CITY = '{$sCity}' AND COUNTRYSHORTCUT = '{$sCountryShortcut}' LIMIT 1");

        return $db->execute()->fetchAssociative();
    }

    public function deleteBulk($aDeleteIds = [])
    {
        if (!empty($aDeleteIds)) {
            foreach ($aDeleteIds as $sDeleteId) {
                $this->delete($sDeleteId);
            }
        }
    }

    public function setInsertQueryValues($aCsvRow , $blEnd = false)
    {
        if ($this->_sCsvInsertQuery === null) {
            $this->_sCsvInsertQuery = 'INSERT INTO fcaddresses (`OXID`, `PLZ`, `CITY`, `COUNTRY`, `COUNTRYSHORTCUT`) VALUES ';
        }

        $this->_sCsvInsertQuery .= "('{$aCsvRow['OXID']}', '{$aCsvRow['PLZ']}', '{$aCsvRow['CITY']}', '{$aCsvRow['COUNTRY']}', '{$aCsvRow['COUNTRYSHORTCUT']}')";
    }

    public function executeCsvInsertQuery()
    {
        if ($this->_sCsvInsertQuery !== null) {
            DatabaseProvider::getDb()->execute(str_replace(')(', '),(', $this->_sCsvInsertQuery));
        }
    }

    public function getIds()
    {
        $db = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();

        $aIds = [];
        $data = $db->select("OXID")->from('fcaddresses')->execute();

        while ($aRow = $data->fetchAssociative()) {
            $aIds[] = $aRow['OXID'];
        }

        return $aIds;
    }

    public function getAddressCount()
    {
        $db = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $db->select('COUNT(OXID) FROM fcaddresses');
        return $db->execute()->fetchAssociative()['COUNT(OXID)'];
    }

    public function loadByColumnValues($aParams) {
        $sWhere = 'WHERE';
        foreach ($aParams as $sColumn => $sValue) {
            if ($sColumn !== array_key_first($aParams)) {
                $sWhere .= ' AND';
            }
            $sWhere .= " {$sColumn} = '{$sValue}'";
        }

        $db = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $db->select("* FROM fcaddresses {$sWhere} LIMIT 1");

        $data = $db->execute();

        if ($data->rowCount() > 0) {
            $this->assign($data->fetchAssociative());
            return true;
        }
        return false;
    }
}