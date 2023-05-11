<?php

namespace Fatchip\AddressValidation\Application\Model;

use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class Address extends MultiLanguageModel
{
    protected static $_aRootBanner = [];

    /**
     * @var string Name of current class
     */
    protected $_sClassName = 'fcaddresses';

    public function __construct()
    {
        parent::__construct();
        $this->init("fcaddresses");
    }

    public function loadIdByParams($aParams)
    {
        $db = ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class)->create();
        $db->select("OXID FROM fcaddresses WHERE CITY = '{$aParams['fcaddresses__city']}' AND PLZ = '{$aParams['fcaddresses__plz']}' AND COUNTRY = '{$aParams['fcaddresses__country']}' AND COUNTRYSHORTCUT = '{$aParams['fcaddresses__countryshortcut']}' LIMIT 1");

        return $db->execute()->fetchAssociative();
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
}