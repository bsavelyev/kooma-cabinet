<?php

namespace app\repositories;

use app\models\FeesModel;
use Yii;
use yii\db\Exception;

class FeesRepository
{
    /**
     * @param FeesModel $model
     * @return mixed
     * @throws Exception
     */
    public function createFee(FeesModel $model)
    {
        return Yii::$app->db->createCommand(
            'SELECT * FROM cabinet.create_fee(
                :service_id,
                :provider_id,
                :value,
                :value_type,
                :start_date,
                :type_id,
                :agent_id,
                :end_date,
                :min_value,
                :max_value
            )',
            [
                ':service_id' => $model->service_id,
                ':provider_id' => $model->provider_id,
                ':value' => $model->value,
                ':type_id' => $model->type_id,
                ':value_type' => $model->value_type,
                ':start_date' => $model->start_date,
                ':agent_id' => $model->agent_id,
                ':end_date' => $model->end_date,
                ':min_value' => $model->min_value,
                ':max_value' => $model->max_value,
            ]
        )->queryScalar();
    }
}
