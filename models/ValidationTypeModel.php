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
class ValidationTypeModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'catalog.validation_type';
    }

    public function attributeLabels()
    {
        return [
            'code' => 'Код',
            'name' => 'Тип значения',
            'descr' => 'Описание типа',
        ];
    }
}
