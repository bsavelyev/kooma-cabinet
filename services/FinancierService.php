<?php

namespace app\services;

use app\controllers\FinancierController;
use app\models\OperationModel;
use app\models\ServiceModel;
use app\models\User;
use app\repositories\FinancierRepository;

class FinancierService
{
    private FinancierRepository $repository;

    public function __construct(FinancierRepository $financierRepository)
    {
        $this->repository = $financierRepository;
    }

    public function createOperation($data, $id, $type, $descr): bool
    {
        $service_name = FinancierController::REPLENISHMENT !== '' ? \Yii::$app->params['replenishmentService'] : \Yii::$app->params['dischargeService'];
        $model = new OperationModel();
        $model->load($data);
        $model->type_id = $type;
        $model->descr = $descr;
        $model->service_id = ServiceModel::findOne(['system_name' => $service_name])->id;
        $model->sender_id = \Yii::$app->user->identity->username;
        $model->payment_account = User::findOne(['id' => $id])->username;

        return $this->repository->createOperation($model);
    }

    public function searchOperation($model): bool
    {
        return $this->repository->searchOperations();
    }
}
