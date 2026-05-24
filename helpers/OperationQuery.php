<?php

namespace app\helpers;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Operation]].
 *
 * @see Operation
 */
class OperationQuery extends ActiveQuery
{
    /**
     * @return app\models\Operation|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @return null|app\models\Operation|array
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
