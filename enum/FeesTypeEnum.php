<?php

namespace app\enum;

final class FeesTypeEnum
{
    public const COMMISSION_TYPE_LOWER = 0;
    public const COMMISSION_TYPE_UPPER = 1;
    public const COMMISSION_TYPE_UPPER_PART = 2;

    public static function values(): array
    {
        return [
            self::COMMISSION_TYPE_LOWER => 'lower',
            self::COMMISSION_TYPE_UPPER => 'upper',
            self::COMMISSION_TYPE_UPPER_PART => 'upper part',
        ];
    }
}
