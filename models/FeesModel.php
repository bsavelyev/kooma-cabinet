<?php

namespace app\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

class FeesModel extends ActiveRecord
{
    // Виртуальные свойства для поиска
    public $service_system_name;

    public $provider_username;

    public $agent_username;

    public static function tableName(): string
    {
        return 'kooma.fees';
    }

    public function rules(): array
    {
        return [
            [['value', 'min_value', 'max_value'], 'double'],
            [['value', 'min_value', 'max_value'], 'filter', 'filter' => [$this, 'filterValue']],
            [['start_date', 'end_date', 'create_date', 'status_change_date'], 'string'],
            [['start_date', 'end_date'], 'required'],
            [['start_date', 'end_date', 'create_date', 'status_change_date'], 'filter', 'filter' => [$this, 'filterDate']],
            [['id', 'service_id', 'provider_id', 'agent_id', 'status', 'value_type', 'type_id'], 'integer'],
            [['id', 'service_id', 'provider_id', 'agent_id', 'status', 'value_type', 'type_id'], 'filter', 'filter' => 'intval'],
            [['service_system_name', 'provider_username', 'agent_username'], 'safe'],
        ];
    }

    public function filterValue($value)
    {
        return is_numeric($value) ? $value : null;
    }

    public function filterDate($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = new Expression("TO_TIMESTAMP(:string, 'YYYY-MM-DD HH24:MI:SS.US')", [':string' => $value]);

        return (new Query())->select($value)->scalar();
    }

    public function attributeLabels(): array
    {
        return [
            'service_id' => 'ID сервиса',
            'provider_id' => 'ID провайдера',
            'agent_id' => 'ID агента',
            'status' => 'Статус',
            'value' => 'Значение',
            'value_type' => 'Тип значения',
            'type_id' => 'Тип комиссии',
            'start_date' => 'Дата запуска',
            'end_date' => 'Дата окончания',
            'create_date' => 'Дата создания',
            'status_change_date' => 'Дата изменения статуса',
            'min_value' => 'Минимальное значение',
            'max_value' => 'Максимальное значение',
            'service_system_name' => 'Название сервиса',
            'provider_username' => 'Имя провайдера',
            'agent_username' => 'Имя агента',
        ];
    }

    public function getService()
    {
        return $this->hasOne(ServiceModel::class, ['id' => 'service_id']);
    }

    public function getProvider()
    {
        return $this->hasOne(User::class, ['id' => 'provider_id'])->alias('provider');
    }

    public function getAgent()
    {
        return $this->hasOne(User::class, ['id' => 'agent_id'])->alias('agent');
    }

    /**
     * Создает data provider с поиском
     *
     * @param mixed $params
     */
    public function search($params): ActiveDataProvider
    {
        $query = self::find()
            ->joinWith(['provider', 'agent', 'service'])
            ->select([
                'fees.*',
                'provider.username AS provider_name',
                'agent.username AS agent_name',
            ])
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        $this->load($params);

        if ($this->service_system_name) {
            $query->andWhere(['ILIKE', 'service.system_name', $this->service_system_name]);
        }
        if ($this->provider_username) {
            $query->andWhere(['ILIKE', 'provider.username', $this->provider_username]);
        }
        if ($this->agent_username) {
            $query->andWhere(['ILIKE', 'agent.username', $this->agent_username]);
        }

        $query->andFilterWhere(['value' => $this->value]);
        $query->andFilterWhere(['min_value' => $this->min_value]);
        $query->andFilterWhere(['max_value' => $this->max_value]);

        return $dataProvider;
    }
}
