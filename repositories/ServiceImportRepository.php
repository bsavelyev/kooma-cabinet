<?php

namespace app\repositories;

use app\models\FieldsTypeModel;
use app\models\ServiceModel;
use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderFieldsValidationModel;
use app\models\ServiceProviderModel;
use app\models\ValidationTypeModel;
use RuntimeException;
use yii\db\Expression;
use yii\db\Query;

class ServiceImportRepository
{
    /** @var array<string, int>|null */
    private $fieldTypeCodes;

    /** @var array<string, int>|null */
    private $validationTypeCodes;

    /**
     * @return string
     */
    public function getDbTimestamp()
    {
        return (new Query())->select(new Expression('NOW()'))->scalar();
    }

    /**
     * @param string $systemName
     * @return bool
     */
    public function serviceExists($systemName)
    {
        return ServiceModel::find()->where(['system_name' => $systemName])->exists();
    }

    /**
     * @param string $systemName
     * @param string $userName
     * @param string $date
     * @return ServiceModel
     */
    public function createService($systemName, $userName, $date)
    {
        $service = new ServiceModel();
        $service->setAttributes([
            'system_name' => $systemName,
            'user_name' => $userName,
            'status' => 1,
            'create_date' => $date,
            'icon' => '',
        ]);

        if (!$service->save()) {
            throw new RuntimeException('Failed to save service: ' . $systemName);
        }

        return $service;
    }

    /**
     * @param int $serviceId
     * @param int $providerId
     * @param string $date
     * @param string $externalServiceId
     * @return ServiceProviderModel
     */
    public function createProvider($serviceId, $providerId, $date, $externalServiceId)
    {
        $provider = new ServiceProviderModel();
        $provider->setAttributes([
            'service_id' => $serviceId,
            'provider_id' => $providerId,
            'status' => 1,
            'descr' => 'generated provider',
            'create_date' => $date,
            'external_service_id' => $externalServiceId,
        ]);

        if (!$provider->save()) {
            throw new RuntimeException('Failed to save service provider');
        }

        return $provider;
    }

    /**
     * @param int $serviceProviderId
     * @param string $name
     * @param string $fieldTypeName
     * @return ServiceProviderFieldsModel
     */
    public function createField($serviceProviderId, $name, $fieldTypeName)
    {
        $field = new ServiceProviderFieldsModel();
        $field->setAttributes([
            'service_provider_id' => $serviceProviderId,
            'name' => $name,
            'type' => $this->getFieldTypeCode($fieldTypeName),
            'descr' => 'generated field',
        ]);

        if (!$field->save()) {
            throw new RuntimeException('Failed to save service field: ' . $name);
        }

        return $field;
    }

    /**
     * @param int $fieldId
     * @param string $validationTypeName
     * @param string $descr
     */
    public function createValidation($fieldId, $validationTypeName, $descr)
    {
        $validation = new ServiceProviderFieldsValidationModel();
        $validation->setAttributes([
            'service_provider_field_id' => $fieldId,
            'param' => [],
            'type' => $this->getValidationTypeCode($validationTypeName),
            'descr' => $descr,
        ]);

        if (!$validation->save()) {
            throw new RuntimeException('Failed to save field validation: ' . $validationTypeName);
        }
    }

    /**
     * @param int $fieldId
     * @param int $validationTypeCode
     * @return bool
     */
    public function validationExists($fieldId, $validationTypeCode)
    {
        return ServiceProviderFieldsValidationModel::find()
            ->where([
                'service_provider_field_id' => $fieldId,
                'type' => $validationTypeCode,
            ])
            ->exists();
    }

    /**
     * @param string $name
     * @return int
     */
    public function getFieldTypeCode($name)
    {
        $this->loadFieldTypes();
        if (!isset($this->fieldTypeCodes[$name])) {
            throw new RuntimeException('Unknown field type: ' . $name);
        }

        return $this->fieldTypeCodes[$name];
    }

    /**
     * @param string $name
     * @return int
     */
    public function getValidationTypeCode($name)
    {
        $this->loadValidationTypes();
        if (!isset($this->validationTypeCodes[$name])) {
            throw new RuntimeException('Unknown validation type: ' . $name);
        }

        return $this->validationTypeCodes[$name];
    }

    private function loadFieldTypes()
    {
        if ($this->fieldTypeCodes !== null) {
            return;
        }

        $this->fieldTypeCodes = [];
        foreach (FieldsTypeModel::find()->all() as $row) {
            $this->fieldTypeCodes[$row->name] = (int) $row->code;
        }
    }

    private function loadValidationTypes()
    {
        if ($this->validationTypeCodes !== null) {
            return;
        }

        $this->validationTypeCodes = [];
        foreach (ValidationTypeModel::find()->all() as $row) {
            $this->validationTypeCodes[$row->name] = (int) $row->code;
        }
    }
}
