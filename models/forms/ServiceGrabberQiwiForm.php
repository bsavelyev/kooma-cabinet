<?php

namespace app\models\forms;

use yii\base\Model;
use yii\web\UploadedFile;

class ServiceGrabberQiwiForm extends Model
{
    /** @var UploadedFile|null */
    public $file;

    /** @var int|string|null */
    public $user_id;

    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['file'], 'file', ['extensions' => 'xlsx']],
        ];
    }
}
