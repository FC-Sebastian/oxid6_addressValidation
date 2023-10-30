<?php

namespace Fatchip\AddressValidation\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

class Events
{

    /**
     * Alters db and updates db views on module activation
     *
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function onActivate()
    {
        self::alterDbTables();
        self::updateDbViews();
    }

    /**
     * Updates db views
     *
     * @return void
     */
    protected static function updateDbViews()
    {
        if (Registry::getSession()->getVariable('malladmin')) {
            $oMeta = oxNew(DbMetaDataHandler::class);
            $oMeta->updateViews();
        }
    }

    /**
     * Adds table and columns to db if they don't exist
     *
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function alterDbTables()
    {
        self::addTableIfNotExists();
        self::addColumnIfNotExists( 'PLZ', "VARCHAR(50) COLLATE 'utf8_general_ci'");
        self::addColumnIfNotExists( 'CITY', "VARCHAR(255) COLLATE 'utf8_general_ci'");
        self::addColumnIfNotExists( 'COUNTRY', "VARCHAR(255) COLLATE 'utf8_general_ci'");
        self::addColumnIfNotExists( 'COUNTRYSHORTCUT', "CHAR(32) COLLATE 'latin1_general_ci'");
    }

    /**
     * Checks whether fcaddresses table exist and if not creates it
     *
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function addTableIfNotExists()
    {
        $oDb = DatabaseProvider::getDb();

        if (count($oDb->getAll("SHOW TABLES LIKE 'fcaddresses'")) === 0) {
            $oDb->execute("CREATE TABLE fcaddresses (`OXID` CHAR(32) NOT NULL COLLATE 'latin1_general_ci', PRIMARY KEY (`OXID`))");
        }
    }

    /**
     * Adds column to fcaddresses table if column doesn't exist
     *
     * @param string $sColumnName
     * @param string $sColumnParams
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected static function addColumnIfNotExists($sColumnName, $sColumnParams)
    {
        if (count(DatabaseProvider::getDb()->getAll("SHOW COLUMNS FROM fcaddresses LIKE '{$sColumnName}'")) === 0) {
            DatabaseProvider::getDb()->execute("ALTER TABLE fcaddresses ADD {$sColumnName} {$sColumnParams}");
        }
    }
}