<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Limits Payment Account Model
 *
 * @property integer id
 * @property integer period
 * @property integer value
 * @property integer value_type
 * @property integer merchant_id
 */
class LimitsPaymentAccountByMerchantModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'limits.payment_account_by_merchant';
    }

    public function rules()
    {
        return [
            [['period', 'value', 'value_type', 'merchant_id'], 'required'],
            [['period', 'value_type', 'merchant_id'], 'integer'],
            [['value'], 'double'],
            [['period', 'value_type', 'merchant_id'], 'filter', 'filter' => 'intval'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID лимита',
            'period' => 'Период',
            'value' => 'Значение',
            'value_type' => 'Тип значения',
            'merchant_id' => 'Мерчант',
        ];
    }

    public function getSubject()
    {
        return $this->hasOne(User::class, ['id' => 'merchant_id']);
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
