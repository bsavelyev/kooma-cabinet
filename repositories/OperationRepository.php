<?php

namespace app\repositories;

use app\models\OperationModel;

class OperationRepository
{
    /**
     * @param int $id
     * @return OperationModel|null
     */
    public function findById($id)
    {
        return OperationModel::findOne(['id' => $id]);
    }

    /**
     * @param string $extId
     * @return OperationModel|null
     */
    public function findByExtId($extId)
    {
        return OperationModel::find()->where(['ext_id' => $extId])->one();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function changeStatus($id)
    {
        $operation = $this->findById($id);
        if ($operation === null) {
            return false;
        }

        return $operation->changeStatus($id);
    }
}
