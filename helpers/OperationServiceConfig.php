<?php

namespace app\helpers;

use Yii;

class OperationServiceConfig
{
    /**
     * @return bool
     */
    public static function isProduction()
    {
        $prodHost = Yii::$app->params['operation']['prodHost'] ?? 'cabinet.koomapay.kz';
        $serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';

        return $prodHost !== '' && strpos($serverName, $prodHost) !== false;
    }

    /**
     * @return int[]
     */
    public static function getUlServiceIds()
    {
        $config = Yii::$app->params['operation']['ulServiceIds'];
        $key = self::isProduction() ? 'prod' : 'test';

        return $config[$key];
    }

    /**
     * @return int[]
     */
    public static function getFlServiceIds()
    {
        $config = Yii::$app->params['operation']['flServiceIds'];
        $key = self::isProduction() ? 'prod' : 'test';

        return $config[$key];
    }

    /**
     * @param int $serviceId
     * @return bool
     */
    public static function isUlServiceId($serviceId)
    {
        return in_array((int) $serviceId, self::getUlServiceIds(), true);
    }
}
