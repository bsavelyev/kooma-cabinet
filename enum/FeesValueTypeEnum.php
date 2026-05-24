<?php

namespace app\enum;

final class FeesValueTypeEnum
{
    public const PERCENT = 0;
    public const FIXED = 1;

    public static function values(): array
    {
        return [
            self::PERCENT => 'percent',
            self::FIXED => 'fixed',
        ];
    }
}
