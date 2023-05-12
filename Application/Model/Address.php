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