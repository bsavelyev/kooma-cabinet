<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $name
 * @property int $type
 * @property string|null $description
 * @property string|null $rule_name
 * @property resource|null $data
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AuthItemModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'kooma.auth_item';
    }

    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['name', 'description', 'rule_name'], 'string'],
            [['type', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert || empty($this->created_at)) {
            $this->created_at = time();
        }
        $this->updated_at = time();

        return true;
    }
}
