<?php

namespace app\helpers;

use app\models\FieldsTypeModel;
use app\models\providers\Wooppay;
use app\models\ServiceModel;
use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderFieldsValidationModel;
use app\models\ServiceProviderModel;
use app\models\ValidationTypeModel;
use yii\db\Expression;
use yii\db\Query;

class ServiceSaver
{
    /**
     * @return array{}|array{0: non-falsy-string, 1?: non-falsy-string}
     */
    public static function saveService(string $services_ids, int $user_id): array
    {
        $services_ids = explode(',', $services_ids);
        $services = Wooppay::getServices($services_ids);

        $result = [];

        foreach ($services as $serviceW) {
            // Проверяем, существует ли уже сервис с таким system_name
            $existingService = ServiceModel::findOne(['system_name' => $serviceW['name']]);
            if (null !== $existingService) {
                $result[] = [
                    'system_name' => $serviceW['name'],
                    'user_name' => $serviceW['title'],
                    'status' => 'exists',
                ];

                continue; // переходим к следующему сервису
            }

            $date = new Expression('NOW()');
            $date = (new Query())->select($date)->scalar();

            $service = new ServiceModel();
            $service->setAttributes([
                'system_name' => $serviceW['name'],
                'user_name' => $serviceW['title'],
                'status' => 1,
                'create_date' => $date,
                'icon' => '',
            ]);
            $service->save();

            $serviceProvider = new ServiceProviderModel();
            $serviceProvider->setAttributes([
                'service_id' => $service->id,
                'provider_id' => $user_id,
                'status' => 1,
                'descr' => 'generated provider',
                'create_date' => $date,
                'external_service_id' => $serviceW['name'],
            ]);
            $serviceProvider->save();

            foreach ($serviceW['fields'] as $field) {
                if ('txn_id' == $field['name']) {
                    $field['type'] = 'hidden';
                }
                $serviceProviderFields = new ServiceProviderFieldsModel();
                $serviceProviderFields->setAttributes([
                    'service_provider_id' => $serviceProvider->id,
                    'name' => $field['name'],
                    'type' => FieldsTypeModel::findOne(['name' => $field['type']])->code,
                    'descr' => 'generated field',
                ]);
                $serviceProviderFields->save();

                if ($field['validations']) {
                    foreach ($field['validations'] as $validation) {
                        if (in_array($validation['type'], ['required', 'amount', 'string'])) {
                            $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                            if ('amount' == $validation['type']) {
                                $validation['type'] = 'integer';
                            }
                            $serviceProviderFieldsValidator->setAttributes([
                                'service_provider_field_id' => $serviceProviderFields->id,
                                'param' => [],
                                'type' => ValidationTypeModel::findOne(['name' => $validation['type']])->code,
                                'descr' => $validation['type'],
                            ]);
                            $serviceProviderFieldsValidator->save();
                        }
                    }
                }

                if (!$field['validations'] && 'amount' != $field['name'] && 'account' != $field['name']) {
                    $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                    $serviceProviderFieldsValidator->setAttributes([
                        'service_provider_field_id' => $serviceProviderFields->id,
                        'param' => [],
                        'type' => ValidationTypeModel::findOne(['name' => 'string'])->code,
                        'descr' => 'string',
                    ]);
                    $serviceProviderFieldsValidator->save();
                }

                if ('account' === $field['name']) {
                    $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                    $serviceProviderFieldsValidator->setAttributes([
                        'service_provider_field_id' => $serviceProviderFields->id,
                        'param' => [],
                        'type' => ValidationTypeModel::findOne(['name' => 'required'])->code,
                        'descr' => 'required',
                    ]);

                    if (!ServiceProviderFieldsValidationModel::findOne(['service_provider_field_id' => $serviceProviderFields->id,
                        'type' => ValidationTypeModel::findOne(['name' => 'required'])->code, ])) {
                        $serviceProviderFieldsValidator->save();
                    }

                    $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                    $serviceProviderFieldsValidator->setAttributes([
                        'service_provider_field_id' => $serviceProviderFields->id,
                        'param' => [],
                        'type' => ValidationTypeModel::findOne(['name' => 'string'])->code,
                        'descr' => 'string',
                    ]);

                    if (!ServiceProviderFieldsValidationModel::findOne(['service_provider_field_id' => $serviceProviderFields->id,
                        'type' => ValidationTypeModel::findOne(['name' => 'string'])->code, ])) {
                        $serviceProviderFieldsValidator->save();
                    }
                }

                if ('amount' === $field['name']) {
                    $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                    $serviceProviderFieldsValidator->setAttributes([
                        'service_provider_field_id' => $serviceProviderFields->id,
                        'param' => [],
                        'type' => ValidationTypeModel::findOne(['name' => 'required'])->code,
                        'descr' => 'required',
                    ]);

                    if (!ServiceProviderFieldsValidationModel::findOne(['service_provider_field_id' => $serviceProviderFields->id,
                        'type' => ValidationTypeModel::findOne(['name' => 'required'])->code, ])) {
                        $serviceProviderFieldsValidator->save();
                    }

                    $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                    $serviceProviderFieldsValidator->setAttributes([
                        'service_provider_field_id' => $serviceProviderFields->id,
                        'param' => [],
                        'type' => ValidationTypeModel::findOne(['name' => 'double'])->code,
                        'descr' => 'double',
                    ]);

                    if (!ServiceProviderFieldsValidationModel::findOne(['service_provider_field_id' => $serviceProviderFields->id,
                        'type' => ValidationTypeModel::findOne(['name' => 'double'])->code, ])) {
                        $serviceProviderFieldsValidator->save();
                    }
                }
            }

            $result[] = [
                'service_id' => $service->id,
                'system_name' => $service->system_name,
                'user_name' => $service->user_name,
                'status' => 'created',
            ];
        }

        // Выводим через setFlash с группировкой
        $created = array_filter($result, fn (array $s): bool => 'created' === $s['status']);
        $exists = array_filter($result, fn (array $s): bool => 'exists' === $s['status']);

        $msg = [];

        if ($created !== []) {
            $msg[] = 'Созданы сервисы: '.implode(', ', array_column($created, 'user_name'));
        }

        if ($exists !== []) {
            $msg[] = 'Уже существующие сервисы: '.implode(', ', array_column($exists, 'user_name'));
        }

        return $msg;
    }
}
