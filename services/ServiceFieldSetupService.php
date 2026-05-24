<?php

namespace app\services;

use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderModel;
use app\repositories\ServiceImportRepository;

class ServiceFieldSetupService
{
    /** @var ServiceImportRepository */
    private $repository;

    public function __construct(ServiceImportRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ServiceProviderModel $provider
     * @param array[] $fields
     */
    public function setupWooppayFields(ServiceProviderModel $provider, array $fields)
    {
        foreach ($fields as $field) {
            if ($field['name'] === 'txn_id') {
                $field['type'] = 'hidden';
            }

            $serviceField = $this->repository->createField(
                $provider->id,
                $field['name'],
                $field['type']
            );

            $this->applyWooppayFieldValidations($serviceField, $field);
        }
    }

    /**
     * @param ServiceProviderModel $provider
     */
    public function setupQiwiFields(ServiceProviderModel $provider)
    {
        $fields = [
            ['name' => 'account', 'type' => 'text', 'validations' => ['required', 'string']],
            ['name' => 'amount', 'type' => 'text', 'validations' => ['required', 'double']],
            ['name' => 'txn_id', 'type' => 'hidden', 'validations' => ['string']],
            ['name' => 'extra', 'type' => 'text', 'validations' => ['string']],
        ];

        foreach ($fields as $field) {
            $serviceField = $this->repository->createField(
                $provider->id,
                $field['name'],
                $field['type']
            );

            foreach ($field['validations'] as $validation) {
                $validationType = $validation === 'double' ? 'integer' : $validation;
                $this->repository->createValidation($serviceField->id, $validationType, $validationType);
            }
        }
    }

    /**
     * @param ServiceProviderFieldsModel $serviceField
     * @param array $field
     */
    private function applyWooppayFieldValidations(ServiceProviderFieldsModel $serviceField, array $field)
    {
        if (!empty($field['validations'])) {
            foreach ($field['validations'] as $validation) {
                if (!in_array($validation['type'], ['required', 'amount', 'string'], true)) {
                    continue;
                }

                $validationType = $validation['type'] === 'amount' ? 'integer' : $validation['type'];
                $this->repository->createValidation(
                    $serviceField->id,
                    $validationType,
                    $validationType
                );
            }
        }

        if (empty($field['validations']) && $field['name'] !== 'amount' && $field['name'] !== 'account') {
            $this->repository->createValidation($serviceField->id, 'string', 'string');
        }

        if ($field['name'] === 'account') {
            $this->ensureValidation($serviceField->id, 'required');
            $this->ensureValidation($serviceField->id, 'string');
        }

        if ($field['name'] === 'amount') {
            $this->ensureValidation($serviceField->id, 'required');
            $this->ensureValidation($serviceField->id, 'double');
        }
    }

    /**
     * @param int $fieldId
     * @param string $validationTypeName
     */
    private function ensureValidation($fieldId, $validationTypeName)
    {
        $code = $this->repository->getValidationTypeCode($validationTypeName);
        if ($this->repository->validationExists($fieldId, $code)) {
            return;
        }

        $this->repository->createValidation($fieldId, $validationTypeName, $validationTypeName);
    }
}
