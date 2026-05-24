<?php

namespace tests\unit\helpers;

use app\helpers\OperationServiceConfig;
use Codeception\Test\Unit;

class OperationServiceConfigTest extends Unit
{
    protected function _before()
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
    }

    public function testIsUlServiceIdInTestEnvironment()
    {
        verify(OperationServiceConfig::isUlServiceId(2797))->true();
        verify(OperationServiceConfig::isUlServiceId(999999))->false();
    }

    public function testGetUlServiceIdsReturnsTestConfig()
    {
        verify(OperationServiceConfig::getUlServiceIds())->equals([2797, 2796, 2798]);
    }

    public function testIsProductionWhenHostMatches()
    {
        $_SERVER['SERVER_NAME'] = 'cabinet.koomapay.kz';
        verify(OperationServiceConfig::isProduction())->true();

        $_SERVER['SERVER_NAME'] = 'localhost';
        verify(OperationServiceConfig::isProduction())->false();
    }
}
