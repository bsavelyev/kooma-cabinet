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
class LimitValueTypeModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'catalog.limit_value_type';
    }

    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'name' => 'Тип значения',
            'descr' => 'Описание типа',
        ];
    }

    public function getLimitsPaymentAccount()
    {
        return $this->hasOne(LimitsPaymentAccountModel::class, ['value_type' => 'code']);
    }
}
