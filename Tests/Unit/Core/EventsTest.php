<?php

namespace Fatchip\AddressValidation\Tests\Unit\Core;

class EventsTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testOnActivate()
    {
        $this->addTeardownSql('DROP TABLE docker_test.fcaddresses');
        $oDb = $this->getDb();

        \Fatchip\AddressValidation\Core\Events::onActivate();
        $this->assertEquals('fcaddresses', $oDb->getOne("SHOW TABLES FROM docker_test LIKE 'fcaddresses'"));
    }
}