<?php

namespace Fatchip\AddressValidation\Tests\Unit\Application\Model;

use \Fatchip\AddressValidation\Application\Model\Address;

class AddressTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        \Fatchip\AddressValidation\Core\Events::alterDbTables();
        $this->addTeardownSql('DROP TABLE docker_test.fcaddresses');
    }

    public function test__construct()
    {
        $oAddress = new Address();
        self::assertEquals('fcaddresses', $oAddress->getCoreTableName());
    }

    /**
     * @dataProvider bulkProvider
     *
     * @param $aIds
     * @param $iExpected
     * @return void
     */
    public function testBulkDelete($aIds, $iExpected)
    {
        $oAddress = $this->getMockBuilder(Address::class)->onlyMethods(['delete'])->getMock();
        $oAddress->expects($this->exactly($iExpected))->method('delete');

        $oAddress->fcDeleteBulk($aIds);
    }

    public function bulkProvider(): array
    {
        return [
            [[], 0],
            [array_fill(0, 5, 'id'), 5],
            [array_fill(0, 10, 'id'), 10]
        ];
    }

    public function testFcSetInsertQueryValues()
    {
        $oAddress = new Address();
        $this->assertEquals(null, $this->getProtectedClassProperty($oAddress, 'fc_sCsvInsertQuery'));
        $aCsvRow = ['OXID' => 'id', 'PLZ' => 12345, 'CITY' => 'Stadt', 'COUNTRY' => 'Land', 'COUNTRYSHORTCUT' => 'XY'];
        $sExpected = 'INSERT INTO fcaddresses (`OXID`, `PLZ`, `CITY`, `COUNTRY`, `COUNTRYSHORTCUT`) VALUES ';
        $sExpected .= "('{$aCsvRow['OXID']}', '{$aCsvRow['PLZ']}', '{$aCsvRow['CITY']}', '{$aCsvRow['COUNTRY']}', '{$aCsvRow['COUNTRYSHORTCUT']}')";

        $oAddress->fcSetInsertQueryValues($aCsvRow);
        $this->assertEquals($sExpected, $this->getProtectedClassProperty($oAddress, 'fc_sCsvInsertQuery'));

        return $oAddress;
    }

    /**
     * @depends testFcSetInsertQueryValues
     *
     * @param $oAddress Address
     * @return void
     */
    public function testFcExecuteCsvInsertQuery($oAddress)
    {
        $oDb = $this->getDb();
        $this->assertEmpty( $oDb->getOne('SELECT COUNT(OXID) FROM fcadresses'));

        $oAddress->fcExecuteCsvInsertQuery();
        $this->assertEquals('1',  $oDb->getOne('SELECT COUNT(OXID) FROM fcadresses'));
    }

    public function testFcGetIds()
    {
        $aIds = ['id1','id2','id3'];
        $oAddress = new Address();
        $oDb = $this->getDb();
        $oDb->execute('INSERT INTO fcaddresses (`OXID`) VALUES '."('{$aIds[0]}'),('{$aIds[1]}'),('{$aIds[2]}')");


        $this->assertEquals($aIds, $oAddress->fcGetIds());
    }

    public function testFcGetAddressCount()
    {
        $oDb = $this->getDb();
        $oAddress = new Address();

        $this->assertEquals('0', $oAddress->fcGetAddressCount());

        $oDb->execute('INSERT INTO fcaddresses (`OXID`) VALUES (1),(2),(3)');
        $this->assertEquals('3', $oAddress->fcGetAddressCount());
    }

    public function testFcLoadByColumnValues()
    {
        $oDb = $this->getDb();
        $oAddress = new Address();
        $aParams = ['OXID' => 'id', 'PLZ' => 12345, 'CITY' => 'Stadt', 'COUNTRY' => 'Land', 'COUNTRYSHORTCUT'];

        $sQuery = 'INSERT INTO fcaddresses (`OXID`, `PLZ`, `CITY`, `COUNTRY`, `COUNTRYSHORTCUT`) VALUES ';
        $sQuery .= "('{$aParams['OXID']}', '{$aParams['PLZ']}', '{$aParams['CITY']}', '{$aParams['COUNTRY']}', '{$aParams['COUNTRYSHORTCUT']}')";

        $this->assertFalse($oAddress->fcLoadByColumnValues($aParams));
        $oDb->execute($sQuery);
        $this->assertTrue($oAddress->fcLoadByColumnValues($aParams));
    }
}
