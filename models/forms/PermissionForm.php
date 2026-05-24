<?php

namespace app\models\forms;

use yii\base\Model;

/**
 * Форма назначения permissions роли.
 */
class PermissionForm extends Model
{
    /** @var string[] */
    public $child = [];

    public function rules()
    {
        return [
            [['child'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'child' => 'Permissions',
        ];
    }
}
