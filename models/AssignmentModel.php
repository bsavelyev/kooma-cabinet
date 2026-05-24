<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string $item_name
 * @property int $user_id
 * @property int|null $created_at
 */
class AssignmentModel extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'kooma.auth_assignment';
    }

    public function rules(): array
    {
        return [
            [['item_name', 'user_id', 'created_at'], 'safe']
        ];
    }

    public function getSubject()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
