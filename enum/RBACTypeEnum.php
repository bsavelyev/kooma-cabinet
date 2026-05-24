<?php

namespace app\enum;

final class RBACTypeEnum
{
    public const ROLE = 1;
    public const PERMISSION = 2;

    public static function values(): array
    {
        return [
            self::ROLE => 'role',
            self::PERMISSION => 'permission',
        ];
    }
}
