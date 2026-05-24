<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\jui\DatePicker;

/**
 * Service model.
 *
 * @property int        $id
 * @property string     $system_name
 * @property string     $user_name
 * @property int        $status
 * @property string     $icon
 * @property DatePicker $create_date
 * @property DatePicker $status_change_date
 */
class ServiceModel extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'kooma.service';
    }

    public function rules(): array
    {
        return [
            [['id', 'system_name', 'user_name', 'status', 'icon', 'create_date', 'status_change_date'], 'safe'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if (parent::beforeSave($insert)) {
            $date = new Expression('NOW()');
            $date = (new Query())->select($date)->scalar();
            $this->create_date = $this->create_date ? $this->create_date : $date;
            if ((isset($this->getOldAttributes()['status'])) && ($this->status != $this->getOldAttributes()['status'])) {
                $this->status_change_date = $date;
            }

            return true;
        }

        return false;
    }

    public function attributeLabels(): array
    {
        return [
            'system_name' => 'Имя сервиса в Kooma',
            'user_name' => 'Описание сервиса',
            'status' => 'статус',
            'create_date' => 'Дата создания',
            'icon' => 'URL для логотипа',
            'status_change_date' => 'Дата смены статуса',
        ];
    }

    public function getServiceProvider()
    {
        return $this->hasMany(ServiceProviderModel::class, ['service_id' => 'id']);
    }

    public function getFees()
    {
        return $this->hasOne(FeesModel::class, ['service_id' => 'id']);
    }

    public function getLimitsPaymentAccount()
    {
        return $this->hasOne(LimitsPaymentAccountModel::class, ['service_id' => 'id']);
    }
}
