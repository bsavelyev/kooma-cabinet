<?php

namespace app\helpers;

use app\enum\FeesTypeEnum;
use app\models\FeesModel;
use app\models\FieldsTypeModel;
use app\models\providers\Qiwi;
use app\models\providers\QiwiGetUIProvidersRequest;
use app\models\ServiceModel;
use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderFieldsValidationModel;
use app\models\ServiceProviderModel;
use app\models\ValidationTypeModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Inflector;

class QiwiServiceSaver
{
    /**
     * @return array{}|array{0: non-falsy-string, 1?: non-falsy-string}
     */
    public static function saveService(int $user_id, string $filePath): array
    {
        $request = new QiwiGetUIProvidersRequest();
        $service = new Qiwi();
        $xmlString = $service->sendRequest($request);

        $providers = $xmlString['response']['providers']['getUIProviders']['provider'];

        $services = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        foreach ($rows as $rowNumber => $row) {
            if (empty($row['A'])) {
                unset($rows[$rowNumber]);
            }
        }

        foreach ($rows as $row) {
            $found = false;
            foreach ($providers as $provider) {
                $providerAttrs = (array) $provider['@attributes'];

                $sName = (string) $providerAttrs['sName'];
                if (false === stripos($sName, $row['A'])) {
                    continue;
                }

                $found = true;

                $systemName = $sName;
                $userName = (string) $providerAttrs['tag'] ?? '';

                // Транслитерация systemName
                $transliterated = Inflector::transliterate($systemName);

                // Проверим, существует ли уже такой сервис
                $exists = ServiceModel::find()
                    ->where(['system_name' => $transliterated])
                    ->exists()
                ;

                $status = $exists ? 'exists' : 'new';

                $date = new Expression('NOW()');
                $date = (new Query())->select($date)->scalar();

                // Создаем сервис
                $service = new ServiceModel();
                $service->system_name = $transliterated;
                $service->user_name = $userName;
                $service->status = 1;
                $service->create_date = $date;
                $service->icon = '';
                $service->save();

                // Привязка провайдера
                $serviceProvider = new ServiceProviderModel();
                $serviceProvider->service_id = $service->id;
                $serviceProvider->provider_id = $user_id;
                $serviceProvider->status = 1;
                $serviceProvider->descr = 'generated provider';
                $serviceProvider->create_date = $date;
                $serviceProvider->external_service_id = $sName;
                $serviceProvider->save();

                // Определяем поля
                $fields = [
                    ['name' => 'account', 'type' => 'text', 'validations' => ['required', 'string']],
                    ['name' => 'amount', 'type' => 'text', 'validations' => ['required', 'double']],
                    ['name' => 'txn_id', 'type' => 'hidden', 'validations' => ['string']],
                    ['name' => 'extra', 'type' => 'text', 'validations' => ['string']],
                ];

                // Сохраняем поля
                foreach ($fields as $field) {
                    $serviceProviderFields = new ServiceProviderFieldsModel();
                    $serviceProviderFields->setAttributes([
                        'service_provider_id' => $serviceProvider->id,
                        'name' => $field['name'],
                        'type' => FieldsTypeModel::findOne(['name' => $field['type']])->code,
                        'descr' => 'generated field',
                    ]);
                    $serviceProviderFields->save();

                    foreach ($field['validations'] as $validation) {
                        $serviceProviderFieldsValidator = new ServiceProviderFieldsValidationModel();
                        $validationType = 'double' === $validation ? 'integer' : $validation;
                        $serviceProviderFieldsValidator->setAttributes([
                            'service_provider_field_id' => $serviceProviderFields->id,
                            'param' => [],
                            'type' => ValidationTypeModel::findOne(['name' => $validationType])->code,
                            'descr' => $validationType,
                        ]);
                        $serviceProviderFieldsValidator->save();
                    }
                }

                $model = new FeesModel();

                $now = (new \DateTime())->modify('-1 day')->format('Y-m-d H:i:s.u'); // .u — микросекунды (6 знаков)

                $now = new Expression(
                    "TO_TIMESTAMP(:string, 'YYYY-MM-DD HH24:MI:SS.US')",
                    [':string' => $now]
                );

                $nowPlus4Years = (new \DateTime())->modify('+4 years')->format('Y-m-d H:i:s.u');

                $nowPlus4Years = new Expression(
                    "TO_TIMESTAMP(:string, 'YYYY-MM-DD HH24:MI:SS.US')",
                    [':string' => $nowPlus4Years]
                );

                try {
                    \Yii::$app->db->createCommand('SELECT * FROM cabinet.create_fee(:service_id, :provider_id, :value, :value_type, :start_date, :type_id, :agent_id, :end_date, :min_value, :max_value)', [
                        ':service_id' => $service->id,
                        ':provider_id' => $serviceProvider->id,
                        ':value' => $row['C'],
                        ':type_id' => $model->type_id,
                        ':value_type' => FeesTypeEnum::COMMISSION_TYPE_UPPER_PART,
                        ':start_date' => $now,
                        ':agent_id' => \Yii::$app->params['payment_service']['qiwi']['koomaID'],
                        ':end_date' => $nowPlus4Years,
                        ':min_value' => 0,
                        ':max_value' => 999999,
                    ])->queryScalar();
                } catch (Exception $e) {
                }

                // Собираем результат
                $services[] = [
                    'id' => $service->id,
                    'system_name' => $transliterated,
                    'status' => $status,
                ];
            }

            if (!$found) {
                $noList[] = $row['A'];
            }
        }

        $created = array_filter($services, fn (array $s): bool => 'created' === $s['status']);
        $exists = array_filter($services, fn (array $s): bool => 'exists' === $s['status']);

        $msg = [];

        if (isset($noList)) {
            $msg[] = 'Нет в списки у Qiwi: '.implode(', ', $noList);
        }

        if ($created !== []) {
            $msg[] = 'Созданы сервисы: '.implode(', ', array_column($created, 'user_name'));
        }

        if ($exists !== []) {
            $msg[] = 'Уже существующие сервисы: '.implode(', ', array_column($exists, 'user_name'));
        }

        return $msg;
    }
}
