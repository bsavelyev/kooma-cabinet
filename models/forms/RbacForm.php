<?php

namespace app\models\forms;

use yii\db\ActiveRecord;

/**
 * Rbac model
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property integer $data_UUID
 * @property integer $created_at
 * @property integer $updated_at
 */
class RbacForm extends ActiveRecord
{
    public static function tableName()
    {
        return 'kooma.auth_item';
    }

    public function rules()
    {
        return [
            [['name', 'type', 'description', 'rule_name', 'data (UUID)', 'created_at', 'updated_at'], 'safe']
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->created_at = $this->created_at ? $this->created_at : time();
            $this->updated_at = time();
            return true;
        }

        return false;
    }
}
