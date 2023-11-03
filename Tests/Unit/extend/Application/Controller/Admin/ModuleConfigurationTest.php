<?php

namespace Fatchip\AddressValidation\Tests\Unit\extend\Application\Controller\Admin;

use \Fatchip\AddressValidation\extend\Application\Controller\Admin\ModuleConfiguration;
use Fatchip\AddressValidation\Application\Model\Address;

class ModuleConfigurationTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSave_invalid()
    {
        $_POST['oxid'] = 'fcaddressvalidation';
        $_POST['fc'] = ['csv_enclosure' => '"', 'csv_escape' => '\\', 'csv_separator' => ';'];
        $_FILES['fc_csvFile'] = ['type' => 'wrongType', 'tmp_name' => 'file.csv'];
        $oModuleConfigMock = $this->getMockBuilder(ModuleConfiguration::class)
            ->onlyMethods(['fcSaveAndDelete', 'fcValidateHeaders'])
            ->getMock();

        $oModuleConfigMock->save();
        $this->assertTrue($oModuleConfigMock->fcGetTypeInvalid());

        $_FILES['fc_csvFile']['type'] = 'text/csv';

        $oModuleConfigMock->save();
        $this->assertTrue($oModuleConfigMock->fcGetHeadersInvalid());
    }

    public function testSave_valid()
    {
        $_POST['oxid'] = 'fcaddressvalidation';
        $_POST['fc'] = ['csv_enclosure' => '"', 'csv_escape' => '\\', 'csv_separator' => ';'];
        $_FILES['fc_csvFile'] = ['type' => 'text/csv', 'tmp_name' => __DIR__.'/test.csv'];

        $oTestCsv = fopen(__DIR__.'/test.csv','w');
        fputcsv($oTestCsv, ["PLZ", "City", "Country", "Country-Shortcut"], $_POST['fc']['csv_separator'], $_POST['fc']['csv_enclosure'], $_POST['fc']['csv_enclosure']);
        fputcsv($oTestCsv, ['12345','Stadt','Land','XY'], $_POST['fc']['csv_separator'], $_POST['fc']['csv_enclosure'], $_POST['fc']['csv_enclosure']);
        fputcsv($oTestCsv, ['123456','Stadt','Land','XY'], $_POST['fc']['csv_separator'], $_POST['fc']['csv_enclosure'], $_POST['fc']['csv_enclosure']);
        fclose($oTestCsv);

        $oAddressMock = $this->getMockBuilder(Address::class)
            ->onlyMethods(['fcSetInsertQueryValues','fcExecuteCsvInsertQuery','fcDeleteBulk','fcGetIds'])
            ->getMock();
        $oModuleConfigMock = $this->getMockBuilder(ModuleConfiguration::class)
            ->onlyMethods(['fcGetAddress'])
            ->getMock();
        $oAddressMock->expects($this->once())->method('fcExecuteCsvInsertQuery');
        $oAddressMock->expects($this->once())->method('fcDeleteBulk');
        $oAddressMock->expects($this->once())->method('fcGetIds')->willReturn([md5('12345'.'Stadt'.'XY')]);
        $oModuleConfigMock->expects($this->once())->method('fcGetAddress')->willReturn($oAddressMock);

        $oModuleConfigMock->save();
        $this->assertTrue($oModuleConfigMock->fcGetComplete());
    }

    /**
     * @dataProvider setterProvider
     *
     * @param $sMethod
     * @param $sValue
     * @return void
     */
    public function testSetter($sMethod, $sProperty, $sValue)
    {
        $oModuleConfig = new ModuleConfiguration();
        $oModuleConfig->$sMethod($sValue);

        $this->assertEquals($sValue, $this->getProtectedClassProperty($oModuleConfig, $sProperty));
    }

    public function setterProvider()
    {
        return [
           ['fcSetSeparator', 'fc_sSeparator', 'value'],
           ['fcSetEnclosure', 'fc_sEnclosure', 'value'],
           ['fcSetEscape', 'fc_sEscape', 'value']
        ];
    }

    /**
     * @dataProvider getterProvider
     *
     * @param $sMethod
     * @param $sProperty
     * @param $sValue
     * @return void
     */
    public function testGetter($sMethod, $sProperty,  $sValue)
    {
        $oModuleConfig = new ModuleConfiguration();
        $this->setProtectedClassProperty($oModuleConfig, $sProperty, $sValue);

        $this->assertEquals($sValue, $oModuleConfig->$sMethod());
    }

    public function getterProvider()
    {
        return [
            ['fcGetSeparator', 'fc_sSeparator', 'value'],
            ['fcGetEnclosure', 'fc_sEnclosure', 'value'],
            ['fcGetEscape', 'fc_sEscape', 'value'],
            ['fcGetTypeInvalid', 'fc_blFileInvalid', true],
            ['fcGetHeadersInvalid', 'fc_blHeadersInvalid', true],
            ['fcGetComplete', 'fc_blComplete', true]
        ];
    }

    public function testFcGetAddressCount()
    {
        $oAddressMock = $this->getMockBuilder(Address::class)->onlyMethods(['fcGetAddressCount'])->getMock();
        $oAddressMock->expects($this->once())->method('fcGetAddressCount')->willReturn(true);

        $oModuleConfig = $this->getMockBuilder(ModuleConfiguration::class)->onlyMethods(['fcGetAddress'])->getMock();
        $oModuleConfig->expects($this->once())->method('fcGetAddress')->willReturn($oAddressMock);

        $this->assertTrue($oModuleConfig->fcGetAddressCount());
    }

    public function testFcGetAddress()
    {
        $oModuleConfigMock = $this->getMockBuilder(ModuleConfiguration::class)->disableOriginalConstructor()->setMethodsExcept(['fcGetAddress'])->getMock();
        $this->assertInstanceOf(Address::class,$oModuleConfigMock->fcGetAddress());
    }
}