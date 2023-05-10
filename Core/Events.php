<?php

namespace Fatchip\AddressValidation\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Registry;

class Events
{

    public static function onActivate()
    {
        self::alterDbTables();
        self::updateDbViews();
    }

    protected static function updateDbViews()
    {
        if (Registry::getSession()->getVariable('malladmin')) {
            $meta = oxNew(DbMetaDataHandler::class);
            $meta->updateViews();
        }
    }

    protected static function alterDbTables()
    {
        self::addTableIfNotExists();
        self::addColumnIfNotExists( 'PLZ', "VARCHAR(50) COLLATE 'utf8_general_ci'");
        self::addColumnIfNotExists( 'CITY', "VARCHAR(255) COLLATE 'utf8_general_ci'");
        self::addColumnIfNotExists( 'COUNTRY', "VARCHAR(255) COLLATE 'utf8_general_ci'");
        self::addColumnIfNotExists( 'COUNTRYSHORTCUT', "CHAR(32) COLLATE 'latin1_general_ci'");
    }

    protected static function addTableIfNotExists()
    {
        $db = DatabaseProvider::getDb();

        if (count($db->getAll("SHOW TABLES LIKE 'fcaddresses'")) === 0) {
            $db->execute("CREATE TABLE fcaddresses (`OXID` CHAR(32) NOT NULL COLLATE 'latin1_general_ci', PRIMARY KEY (`OXID`))");
        }
    }

    protected static function addColumnIfNotExists($sColumnName, $sColumnParams)
    {
        if (count(DatabaseProvider::getDb()->getAll("SHOW COLUMNS FROM fcaddresses LIKE '{$sColumnName}'")) === 0) {
            DatabaseProvider::getDb()->execute("ALTER TABLE fcaddresses ADD {$sColumnName} {$sColumnParams}");
        }
    }
}