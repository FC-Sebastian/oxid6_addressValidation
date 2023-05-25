<?php

namespace Fatchip\AddressValidation\Application\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Address extends MultiLanguageModel
{
    /**
     * Used to store INSERT query for csv import
     *
     * @var string|null
     */
    protected ?string $fc_sCsvInsertQuery = null;

    /**
     * Name of current class
     *
     * @var string
     */
    protected $_sClassName = 'fcaddresses';

    public function __construct()
    {
        parent::__construct();
        $this->init("fcaddresses");
    }

    /**
     * Deletes multiple Addresses using given array of Ids
     *
     * @param array $aDeleteIds
     * @return void
     */
    public function fcDeleteBulk($aDeleteIds = [])
    {
        if (!empty($aDeleteIds)) {
            foreach ($aDeleteIds as $sDeleteId) {
                $this->delete($sDeleteId);
            }
        }
    }

    /**
     * Appends Values from given csv row to csv INSERT query
     *
     * @param array $aCsvRow
     * @return void
     */
    public function fcSetInsertQueryValues($aCsvRow)
    {
        if ($this->fc_sCsvInsertQuery === null) {
            $this->fc_sCsvInsertQuery = 'INSERT INTO fcaddresses (`OXID`, `PLZ`, `CITY`, `COUNTRY`, `COUNTRYSHORTCUT`) VALUES ';
        }

        $this->fc_sCsvInsertQuery .= "('{$aCsvRow['OXID']}', '{$aCsvRow['PLZ']}', '{$aCsvRow['CITY']}', '{$aCsvRow['COUNTRY']}', '{$aCsvRow['COUNTRYSHORTCUT']}')";
    }

    /**
     * Executes the csv INSERT query if it was set
     *
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function fcExecuteCsvInsertQuery()
    {
        if ($this->fc_sCsvInsertQuery !== null) {
            DatabaseProvider::getDb()->execute(str_replace(')(', '),(', $this->fc_sCsvInsertQuery));
        }
    }

    /**
     * Loads all Ids from db and returns them as an array
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function fcGetIds()
    {
        $oDb = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();

        $aIds = [];
        $oData = $oDb->select("OXID")->from('fcaddresses')->execute();

        while ($aRow = $oData->fetchAssociative()) {
            $aIds[] = $aRow['OXID'];
        }

        return $aIds;
    }

    /**
     * Loads and returns total number of Addresses in db
     *
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function fcGetAddressCount()
    {
        $oDb = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $oDb->select('COUNT(OXID) FROM fcaddresses');
        return $oDb->execute()->fetchAssociative()['COUNT(OXID)'];
    }

    public function fcLoadByColumnValues($aParams) {
        $sWhere = 'WHERE';
        foreach ($aParams as $sColumn => $sValue) {
            if ($sColumn !== array_key_first($aParams)) {
                $sWhere .= ' AND';
            }
            $sWhere .= " {$sColumn} = '{$sValue}'";
        }

        $oDb = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $oDb->select("* FROM fcaddresses {$sWhere} LIMIT 1");

        $oData = $oDb->execute();

        if ($oData->rowCount() > 0) {
            $this->assign($oData->fetchAssociative());
            return true;
        }
        return false;
    }
}