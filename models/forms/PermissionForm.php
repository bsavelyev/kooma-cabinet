<?php

namespace app\models\forms;

use yii\db\ActiveRecord;

/**
 * Permission model
 *
 * @property string $parent
 * @property string|array $child
 */
class PermissionForm extends ActiveRecord
{
    public static function tableName()
    {
        return 'kooma.auth_item_child';
    }

    public function rules()
    {
        return [
            [['parent', 'child'], 'safe']
        ];
    }

    public function multipleSave($childes): void
    {
        \Yii::$app->db->createCommand()->batchInsert(PermissionForm::tableName(), $this->attributes(), $childes)->execute();
    }
}
