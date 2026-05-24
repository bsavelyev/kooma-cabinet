<?php

namespace app\models\search;

use app\models\OperationStatusHistoryModel;
use yii\db\Query;

class OperationStatusHistorySearch
{
    /**
     * @param array $params
     * @return Query
     */
    public function buildQuery(array $params)
    {
        $query = OperationStatusHistoryModel::find()->alias('osh')
            ->innerJoin('kooma.operation o', 'o.id = osh.operation_id');

        $query->andFilterWhere([
            'osh.id' => isset($params['id']) ? $params['id'] : null,
            'osh.status' => isset($params['status']) ? $params['status'] : null,
            'osh.success' => (isset($params['success']) ? $params['success'] : '') !== ''
                ? (bool) $params['success']
                : null,
        ]);

        $operationId = trim((string) (isset($params['operation_id']) ? $params['operation_id'] : ''));
        if ($operationId !== '') {
            if (strpos($operationId, ',') !== false) {
                $extIds = array_filter(array_map('trim', explode(',', $operationId)));
                $query->andFilterWhere(['in', 'o.ext_id', $extIds]);
            } else {
                $query->andFilterWhere(['o.ext_id' => $operationId]);
            }
        }

        return $query;
    }
}
