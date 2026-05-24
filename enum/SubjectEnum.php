<?php

namespace app\enum;

final class SubjectEnum
{
    public const STATUS_BLOCKED = 0;
    public const STATUS_ACTIVE = 1;

    public static function values(): array
    {
        return [
            self::STATUS_BLOCKED => 'blocked',
            self::STATUS_ACTIVE => 'active',
        ];
    }
}
