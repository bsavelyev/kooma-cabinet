<?php

namespace app\models;

use yii\db\ActiveRecord;

class OperationStatusHistoryModel extends ActiveRecord
{
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
            'success' => 'Результат',
        ];
    }

    public function getOperation()
    {
        return $this->hasOne(OperationModel::class, ['id' => 'operation_id']);
    }
}
