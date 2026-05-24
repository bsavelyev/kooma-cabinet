<?php

namespace app\services;

use app\models\OperationModel;
use app\models\ServiceModel;
use app\models\User;
use app\repositories\FinancierRepository;
use Yii;

class FinancierService
{
    /** @var FinancierRepository */
    private $repository;

    public function __construct(FinancierRepository $financierRepository)
    {
        $this->repository = $financierRepository;
    }

    /**
     * @param array $data
     * @param int $userId
     * @param int $type
     * @param string $descr
     * @return int|bool
     */
    public function createOperation($data, $userId, $type, $descr)
    {
        $isReplenishment = $type === OperationModel::TYPE_CASHIN;
        $serviceName = $isReplenishment
            ? Yii::$app->params['replenishmentService']
            : Yii::$app->params['dischargeService'];

        $model = new OperationModel();
        $model->load($data);
        $model->type_id = $type;
        $model->descr = $descr;
        $model->service_id = ServiceModel::findOne(['system_name' => $serviceName])->id;
        $model->sender_id = Yii::$app->user->identity->username;
        $model->payment_account = User::findOne(['id' => $userId])->username;

        return $this->repository->createOperation($model);
    }
}
