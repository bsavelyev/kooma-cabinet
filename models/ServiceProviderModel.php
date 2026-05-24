<?php

namespace app\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class ServiceProviderModel extends ActiveRecord
{
    // Виртуальные свойства для поиска
    public $service_system_name;

    public $subject_username;

    public static function tableName(): string
    {
        return 'kooma.service_provider';
    }

    public function rules(): array
    {
        return [
            [['id', 'service_id', 'provider_id', 'status', 'descr', 'external_service_id', 'create_date', 'status_change_date', 'service_system_name', 'subject_username'], 'safe'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'external_service_id' => 'Имя сервиса у провайдера',
            'status' => 'Статус',
            'provider_id' => 'provider_id',
            'descr' => 'Описание',
            'create_date' => 'Дата создания',
            'status_change_date' => 'Дата смены статуса',
            'service_system_name' => 'Название сервиса',
            'subject_username' => 'Имя провайдера',
        ];
    }

    public function getService()
    {
        return $this->hasOne(ServiceModel::class, ['id' => 'service_id']);
    }

    public function getSubject()
    {
        return $this->hasOne(User::class, ['id' => 'provider_id']);
    }

    /**
     * Создает data provider с поиском
     */
    public function search($params): ActiveDataProvider
    {
        $query = ServiceProviderModel::find()
            ->joinWith(['subject'])
            ->joinWith(['service'])
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        // Загружаем параметры поиска
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // Фильтры с нечувствительностью к регистру
        if ($this->service_system_name !== null && $this->service_system_name !== '') {
            $query->andWhere(['ILIKE', 'service.system_name', $this->service_system_name]);
        }

        if ($this->subject_username !== null && $this->subject_username !== '') {
            $query->andWhere(['ILIKE', 'user.username', $this->subject_username]);
        }

        if ($this->descr !== null && $this->descr !== '') {
            $query->andWhere(['ILIKE', 'service_provider.descr', $this->descr]);
        }

        $query->andFilterWhere(['like', 'service_provider.external_service_id', $this->external_service_id]);

        return $dataProvider;
    }
}
