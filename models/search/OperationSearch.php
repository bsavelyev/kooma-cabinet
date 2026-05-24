<?php

namespace app\models\search;

use app\helpers\DateTimeHelper;
use app\helpers\OperationServiceConfig;
use app\models\OperationModel;
use yii\db\ActiveQuery;
use yii\db\Query;

class OperationSearch
{
    /**
     * @return ActiveQuery
     */
    public function baseQuery()
    {
        return OperationModel::find();
    }

    /**
     * @param array $params
     * @return Query
     */
    public function buildQuery(array $params)
    {
        $query = OperationModel::find()->alias('o')
            ->joinWith(['sender sn', 'receiver rc', 'service s'], false);

        $query->andFilterWhere([
            'o.id' => isset($params['id']) ? $params['id'] : null,
            'o.sender_id' => (isset($params['sender_id']) ? $params['sender_id'] : '') == '0' ? '' : (isset($params['sender_id']) ? $params['sender_id'] : null),
            'o.receiver_id' => (isset($params['receiver_id']) ? $params['receiver_id'] : '') == '0' ? '' : (isset($params['receiver_id']) ? $params['receiver_id'] : null),
            'o.status' => isset($params['status']) ? $params['status'] : null,
            'o.amount' => isset($params['amount']) ? $params['amount'] : null,
            'o.billing_id' => isset($params['billing_id']) ? $params['billing_id'] : null,
            'o.payment_account' => isset($params['payment_account']) ? $params['payment_account'] : null,
            'o.type_id' => isset($params['type_id']) ? $params['type_id'] : null,
            'o.ext_id' => isset($params['ext_id']) ? $params['ext_id'] : null,
        ]);

        $this->applyServiceFilter($query, $params);

        $fromCreateDate = '';
        $toCreateDate = '';
        $fromStatusChangeDate = '';
        $toStatusChangeDate = '';

        if (!empty($params['from_create_date'])) {
            $fromCreateDate = DateTimeHelper::convert(
                $params['from_create_date'],
                DateTimeHelper::TIMEZONE_ALMATY,
                DateTimeHelper::TIMEZONE_UTC
            );
            $toCreateDate = DateTimeHelper::convert(
                $params['to_create_date'],
                DateTimeHelper::TIMEZONE_ALMATY,
                DateTimeHelper::TIMEZONE_UTC
            );
        }

        if (!empty($params['from_status_change_date'])) {
            $fromStatusChangeDate = DateTimeHelper::convert(
                $params['from_status_change_date'],
                DateTimeHelper::TIMEZONE_ALMATY,
                DateTimeHelper::TIMEZONE_UTC
            );
            $toStatusChangeDate = DateTimeHelper::convert(
                $params['to_status_change_date'],
                DateTimeHelper::TIMEZONE_ALMATY,
                DateTimeHelper::TIMEZONE_UTC
            );
        }

        $query->andFilterWhere(['between', 'o.create_date', $fromCreateDate, $toCreateDate]);
        $query->andFilterWhere(['between', 'o.status_change_date', $fromStatusChangeDate, $toStatusChangeDate]);

        $query->select([
            'o.id',
            'sender_login' => 'sn.username',
            'receiver_login' => 'rc.username',
            'o.amount',
            'o.create_date',
            'o.status',
            'o.sender_id',
            'o.receiver_id',
            'o.done_date',
            'o.descr',
            'o.billing_id',
            'o.service_id',
            'o.ext_id',
            'o.payment_account',
            'o.type_id',
            'o.sender_account_type',
            'o.upper_fee_amount',
            'o.status_change_date',
        ]);

        return $query;
    }

    /**
     * @param Query $query
     * @param array $params
     */
    private function applyServiceFilter($query, array $params)
    {
        $serviceId = isset($params['service_id'][0]) ? $params['service_id'][0] : 0;

        if ($serviceId === 'UL') {
            $query->andFilterWhere(['in', 'o.service_id', OperationServiceConfig::getUlServiceIds()]);
        } elseif ($serviceId === 'FL') {
            $query->andFilterWhere(['in', 'o.service_id', OperationServiceConfig::getFlServiceIds()]);
        } else {
            $query->andFilterWhere([
                'o.service_id' => $serviceId == '0' ? '' : $serviceId,
            ]);
        }
    }
}
