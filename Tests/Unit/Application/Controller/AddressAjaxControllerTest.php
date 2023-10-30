<?php

namespace Fatchip\AddressValidation\Tests\Unit\Application\Controller;

use \Fatchip\AddressValidation\Application\Controller\AddressAjaxController;

class AddressAjaxControllerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        \Fatchip\AddressValidation\Core\Events::alterDbTables();
        $sOxid1 = md5('12109BerlinDE');
        $sOxid2 = md5('13353BerlinDE');
        $oAddress = new \Fatchip\AddressValidation\Application\Model\Address();

        $oAddress->fcSetInsertQueryValues(['OXID' => $sOxid1, 'PLZ' => '12109', 'CITY' => 'Berlin', 'COUNTRY' => 'Deutschland', 'COUNTRYSHORTCUT' => 'DE']);
        $oAddress->fcSetInsertQueryValues(['OXID' => $sOxid2, 'PLZ' => '13353', 'CITY' => 'Berlin', 'COUNTRY' => 'Deutschland', 'COUNTRYSHORTCUT' => 'DE']);
        $oAddress->fcExecuteCsvInsertQuery();

        $this->addTeardownSql('DROP TABLE docker_test.fcaddresses');
    }

    /**
     * @dataProvider addressProvider
     *
     * @param $sCountryId
     * @param $sCity
     * @param $sZip
     * @param $sExpected
     * @return void
     */
    public function testFcValidateAddress($sCountryId, $sCity, $sZip, $sExpected)
    {
        $_REQUEST['countryId'] = $sCountryId;
        $_REQUEST['city'] = $sCity;
        $_REQUEST['zip'] = $sZip;
        $this->expectOutputString($sExpected);

        $oAddressAjaxController = $this->getMockBuilder(AddressAjaxController::class)
            ->onlyMethods(['fcKillPHP'])
            ->getMock();

        $oAddressAjaxController->fcValidateAddress();
    }

    public function addressProvider()
    {
        $oCountry = new \OxidEsales\Eshop\Application\Model\Country();

        return [
            ['DE', 'Berlin', '12109', json_encode(['status' => 'valid'])],
            ['ED', 'Berlin', '12109', json_encode(['status' => 'country found', 'country' => $oCountry->getIdByCode('DE')])],
            ['DE', 'Blin', '13353', json_encode(['status' => 'city found', 'city' => 'Berlin'])],
            ['DE', 'Dresden', '01001', json_encode(['status' => 'invalid'])]
        ];
    }
}