<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * ServiceProviderFields model
 *
 * @property int $id
 * @property int $service_provider_id
 * @property string $name
 * @property integer $type
 * @property string $descr
 */
class ServiceProviderFieldsModel extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'kooma.service_provider_fields';
    }

    public function attributeLabels(): array
    {
        return
            [
                'id' => '',
                'service_provider_id' => 'ID провайдера сервиса',
                'provider_id' => 'ID провайдера',
                'name' => 'Имя',
                'type' => 'Тип',
                'descr' => 'Описание'
            ];
    }

    public function getServiceProvider()
    {
        return $this->hasMany(ServiceProviderModel::class, ['id' => 'service_provider_id']);
    }

    public function rules(): array
    {
        return [
            [['id', 'service_provider_id', 'name', 'type', 'descr'], 'safe']
        ];
    }

    public function getSubject()
    {
        return $this->hasOne(User::class, ['id' => 'provider_id']);
    }

}
