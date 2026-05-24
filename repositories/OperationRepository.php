<?php

namespace app\repositories;

use app\enum\OperationStatusEnum;
use app\models\OperationModel;
use Yii;
use yii\db\Exception;

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
     * @return bool|mixed
     * @throws Exception
     */
    public function changeStatus($id)
    {
        $result = Yii::$app->db->createCommand(
            'SELECT * FROM operations.change_status(:id, :status)',
            [
                ':id' => $id,
                ':status' => OperationStatusEnum::STATUS_NEW,
            ]
        )->queryScalar();

        return $result > 0 ? true : $result;
    }

    /**
     * @param mixed $sender
     * @param mixed $recipient
     * @param int $serviceId
     * @param mixed $amount
     * @param string $descr
     * @param string $paymentAccount
     * @param int $typeId
     * @param mixed $accountType
     * @return int
     * @throws Exception
     */
    public function create($sender, $recipient, $serviceId, $amount, $descr, $paymentAccount, $typeId, $accountType)
    {
        return (int) Yii::$app->db->createCommand(
            'SELECT * FROM operations.create(
                :sender,
                :recipient,
                :service_id,
                :amount,
                :ext_id,
                :descr,
                :payment_account,
                :type_id,
                :account_type
            )',
            [
                ':sender' => $sender,
                ':recipient' => $recipient,
                ':service_id' => $serviceId,
                ':amount' => $amount,
                ':ext_id' => null,
                ':descr' => $descr,
                ':payment_account' => $paymentAccount,
                ':type_id' => $typeId,
                ':account_type' => $accountType,
            ]
        )->queryScalar();
    }
}
