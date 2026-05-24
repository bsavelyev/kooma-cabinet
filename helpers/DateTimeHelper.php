<?php

namespace app\helpers;

use DateTime;
use DateTimeZone;

class DateTimeHelper
{
    public const TIMEZONE_UTC = 'UTC';
    public const TIMEZONE_ALMATY = 'Asia/Almaty';

    /**
     * @param string $date
     * @param string $from
     * @param string $to
     * @return string
     */
    public static function convert($date, $from = self::TIMEZONE_UTC, $to = self::TIMEZONE_ALMATY)
    {
        $dateTime = new DateTime($date, new DateTimeZone($from));
        $dateTime->setTimezone(new DateTimeZone($to));

        return $dateTime->format('Y-m-d H:i:s.u');
    }
}
