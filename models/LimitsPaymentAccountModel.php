<?php

namespace app\models;

use kartik\widgets\TimePicker;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

use function PHPUnit\Framework\isEmpty;

/**
 * Limits Payment Account Model
 *
 * @property int $id
 * @property int $period
 * @property int $value
 * @property int $value_type
 * @property int $service_id
 */
class LimitsPaymentAccountModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'limits.payment_account';
    }

    public function rules()
    {
        return [
            [['period', 'value', 'value_type', 'service_id'], 'required'],
            [['period', 'value_type', 'service_id'], 'integer'],
            [['value'], 'double'],
            [['period', 'value_type', 'service_id'], 'filter', 'filter' => 'intval'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID лимита',
            'period' => 'Период',
            'value' => 'Значение',
            'value_type' => 'Тип значения',
            'service_id' => 'Сервиса',
        ];
    }

    public function getService()
    {
        return $this->hasOne(ServiceModel::class, ['id' => 'service_id']);
    }

    public function getLimitPeriod()
    {
        return $this->hasOne(LimitPeriodModel::class, ['code' => 'period']);
    }

    public function getLimitValueType()
    {
        return $this->hasOne(LimitValueTypeModel::class, ['code' => 'value_type']);
    }
}
