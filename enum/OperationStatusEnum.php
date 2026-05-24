<?php

namespace app\enum;

final class OperationStatusEnum
{
    public const STATUS_NEW = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_DONE = 2;
    public const STATUS_DECLINED = 3;
    public const STATUS_REFUND = 4;

    public static function values(): array
    {
        return [
            null => '',
            self::STATUS_NEW => 'новая',
            self::STATUS_PENDING => 'в процессе',
            self::STATUS_DONE => 'проведена',
            self::STATUS_DECLINED => 'откланена',
            self::STATUS_REFUND => 'возврат',
        ];
    }
}
