<?php

namespace app\repositories;

use app\models\OperationModel;

class FinancierRepository
{
    /** @var OperationRepository */
    private $operationRepository;

    public function __construct(OperationRepository $operationRepository)
    {
        $this->operationRepository = $operationRepository;
    }

    /**
     * @param OperationModel $model
     * @return int
     */
    public function createOperation(OperationModel $model)
    {
        return $this->operationRepository->create(
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
