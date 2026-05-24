<?php

namespace app\models;

use yii\db\ActiveRecord;

class AuthItemModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'kooma.auth_item';
    }

}
