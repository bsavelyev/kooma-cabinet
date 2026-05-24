<?php

namespace app\repositories;

use app\models\OperationModel;
use Yii;

class FinancierRepository
{
    public function createOperation(OperationModel $model): bool
    {
        return OperationModel::createOperation(
            $model->sender_id,
            $model->payment_account,
            $model->service_id,
            $model->amount,
            $model->descr,
            $model->payment_account,
            $model->type_id
        );
    }

    public function searchOperations($query, $params): bool
    {
        return OperationModel::createOperation(
            $model->sender,
            $model->recipientName,
            $model->service_id,
            $model->amount,
            $model->descr,
            $model->recipientName,
            $model->type,
        );
    }
}
