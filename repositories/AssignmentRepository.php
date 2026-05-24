<?php

namespace app\repositories;

use app\models\AssignmentModel;

class AssignmentRepository
{
    /**
     * @param int $userId
     * @param string $itemName
     * @return bool
     */
    public function assignRole($userId, $itemName)
    {
        AssignmentModel::deleteAll(['user_id' => $userId]);

        $assignment = new AssignmentModel();
        $assignment->item_name = $itemName;
        $assignment->user_id = $userId;
        $assignment->created_at = time();

        return $assignment->save();
    }
}
