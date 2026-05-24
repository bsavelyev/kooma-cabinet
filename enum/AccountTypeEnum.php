<?php

namespace app\enum;

final class AccountTypeEnum
{
    public const MAIN = 0;
    public const UPPER = 1;
    public const LOWER = 2;

    public static function values(): array
    {
        return [
            self::MAIN => 'основной счет',
            self::UPPER => 'счет верхней комиссии',
            self::LOWER => 'счет нижней комиссии',
        ];
    }
}
