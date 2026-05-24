<?php

namespace app\models\forms;

use yii\base\Model;

class ChangeOperationsStatusForm extends Model
{
    /** @var string */
    public $operations_ids;

    public function rules()
    {
        return [
            [['operations_ids'], 'required'],
            [['operations_ids'], 'string'],
        ];
    }
}
