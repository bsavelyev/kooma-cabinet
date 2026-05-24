<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\db\Query;

class OperationStatusHistoryModel extends ActiveRecord
{
    public const Asia_Almaty = 'Asia/Almaty';
    public const UTC = 'UTC';

    public static function tableName()
    {
        return 'logs.operation_status_history';
    }

    public function rules()
    {
        return [
            [['description'], 'string'],
            [['status'], 'integer'],
            [['operation_id', 'status', 'success'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operation_id' => 'ID операции',
            'status' => 'Статус',
            'create_date' => 'Дата записи',
            'description' => 'Описание',
            'success' => 'Результат'
        ];
    }

    public function search(array $params): Query
    {
        $query = self::find()->alias('osh')
            ->innerJoin('kooma.operation o', 'o.id = osh.operation_id');

            $query->andFilterWhere([
                'osh.id' => $params['id'] ?? null,
                'osh.status' => $params['status'] ?? null,
                'osh.success' => ($params['success'] ?? '') !== '' ? (bool)$params['success'] : null,
            ]);
            $operationId = trim((string)($params['operation_id'] ?? ''));
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

    public function getOperation()
    {
        return $this->hasOne(OperationModel::class, ['id' => 'operation_id']);
    }

    /**
     * Получить ActiveQuery для истории статусов операций
     */
    public static function getQuery(): \yii\db\ActiveQuery
    {
        return self::find();
    }
}
