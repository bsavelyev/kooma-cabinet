<?php

namespace app\models\forms;

use yii\base\Model;

class ServiceGrabberForm extends Model
{
    /** @var string */
    public $services_ids;

    /** @var int|string|null */
    public $user_id;

    public function rules()
    {
        return [
            [['services_ids', 'user_id'], 'required'],
            [['services_ids'], 'string'],
            [['user_id'], 'integer'],
        ];
    }
}
