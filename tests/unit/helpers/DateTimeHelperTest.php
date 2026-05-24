<?php

namespace tests\unit\helpers;

use app\helpers\DateTimeHelper;
use Codeception\Test\Unit;

class DateTimeHelperTest extends Unit
{
    public function testConvertUtcToAlmaty()
    {
        $result = DateTimeHelper::convert(
            '2024-06-01 12:00:00.000000',
            DateTimeHelper::TIMEZONE_UTC,
            DateTimeHelper::TIMEZONE_ALMATY
        );

        verify($result)->equals('2024-06-01 17:00:00.000000');
    }

    public function testConvertSameTimezone()
    {
        $result = DateTimeHelper::convert(
            '2024-01-15 10:30:00.000000',
            DateTimeHelper::TIMEZONE_UTC,
            DateTimeHelper::TIMEZONE_UTC
        );

        verify($result)->equals('2024-01-15 10:30:00.000000');
    }
}
