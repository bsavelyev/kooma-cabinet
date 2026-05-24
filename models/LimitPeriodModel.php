<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Limit Period model
 *
 * @property int $code
 * @property string $name
 * @property string $descr
 */
class LimitPeriodModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'catalog.limit_period';
    }

    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'name' => 'Тип лимита',
            'descr' => 'Описание лимита',
        ];
    }

    public function getLimitsPaymentAccount()
    {
        return $this->hasOne(LimitsPaymentAccountModel::class, ['period' => 'code']);
    }
}
