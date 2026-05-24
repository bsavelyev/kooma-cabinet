<?php

namespace app\repositories;

use app\models\OperationModel;

class FinancierRepository
{
    /**
     * @param OperationModel $model
     * @return int|bool
     */
    public function createOperation(OperationModel $model)
    {
        return OperationModel::createOperation(
            $model->sender_id,
            $model->payment_account,
            $model->service_id,
            $model->amount,
            $model->descr,
            $model->payment_account,
            $model->type_id,
            $model->sender_account_type
        );
    }
}
