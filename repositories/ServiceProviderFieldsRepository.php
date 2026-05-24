<?php

namespace app\repositories;

use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderFieldsValidationModel;

class ServiceProviderFieldsRepository
{
    /**
     * @param int $providerId
     * @return \yii\db\ActiveQuery
     */
    public function getFieldsQueryByProviderId($providerId)
    {
        return ServiceProviderFieldsModel::find()
            ->select('service_provider_fields.*')
            ->from('kooma.service')
            ->innerJoin('kooma.service_provider', 'kooma.service_provider.service_id = kooma.service.id')
            ->innerJoin(
                'kooma.service_provider_fields',
                'kooma.service_provider_fields.service_provider_id = kooma.service_provider.id'
            )
            ->where(['kooma.service_provider.id' => $providerId])
            ->orderBy('id');
    }

    /**
     * @param int $providerId
     */
    public function deleteByProviderId($providerId)
    {
        $fields = ServiceProviderFieldsModel::findAll(['service_provider_id' => $providerId]);

        foreach ($fields as $field) {
            ServiceProviderFieldsValidationModel::deleteAll(['service_provider_field_id' => $field->id]);
        }

        if ($fields !== []) {
            ServiceProviderFieldsModel::deleteAll(['service_provider_id' => $providerId]);
        }
    }
}
