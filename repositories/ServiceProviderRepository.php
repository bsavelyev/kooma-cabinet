<?php

namespace app\repositories;

use app\models\ServiceProviderModel;

class ServiceProviderRepository
{
    /** @var ServiceProviderFieldsRepository */
    private $fieldsRepository;

    public function __construct(ServiceProviderFieldsRepository $fieldsRepository)
    {
        $this->fieldsRepository = $fieldsRepository;
    }

    /**
     * @param int $providerId
     * @return bool
     */
    public function deleteWithDependencies($providerId)
    {
        $provider = ServiceProviderModel::findOne(['id' => $providerId]);
        if ($provider === null) {
            return false;
        }

        $this->fieldsRepository->deleteByProviderId($providerId);

        return (bool) $provider->delete();
    }
}
