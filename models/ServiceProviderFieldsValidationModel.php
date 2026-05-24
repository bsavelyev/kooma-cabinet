<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * ServiceProviderFieldsValidation model
 *
 * @property integer $id
 * @property integer $service_provider_field_id
 * @property string $params
 * @property integer $type
 * @property string $descr
 */
class ServiceProviderFieldsValidationModel extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'kooma.service_provider_fields_validation';
    }

    public function attributeLabels(): array
    {
        return
            [
                'service_provider_field_id' => 'ID поля',
                'params' => 'Параметры',
                'type' => 'Тип',
                'descr' => 'Описание'
            ];
    }

    public function afterFind(): self
    {
        if (isset($this->param)) {
            $this->param = json_encode($this->param);
        }
        return $this;
    }

    public function rules(): array
    {
        return [
            [['id', 'service_provider_field_id', 'param', 'type', 'descr'], 'safe']
        ];
    }
}
