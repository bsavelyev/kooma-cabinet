<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Limit Period model
 *
 * @property integer code
 * @property string name
 * @property string descr
 */
class FieldsTypeModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'catalog.fields_type';
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
