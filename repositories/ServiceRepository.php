<?php

namespace app\repositories;

use app\models\FeesModel;
use app\models\ServiceModel;
use app\models\ServiceProviderModel;

class ServiceRepository
{
    /** @var ServiceProviderRepository */
    private $providerRepository;

    public function __construct(ServiceProviderRepository $providerRepository)
    {
        $this->providerRepository = $providerRepository;
    }

    /**
     * @param int $serviceId
     * @return bool
     */
    public function deleteWithDependencies($serviceId)
    {
        $service = ServiceModel::findOne(['id' => $serviceId]);
        if ($service === null) {
            return false;
        }

        $provider = ServiceProviderModel::findOne(['service_id' => $serviceId]);
        if ($provider !== null) {
            $this->providerRepository->deleteWithDependencies($provider->id);
        }

        FeesModel::deleteAll(['service_id' => $serviceId]);

        return (bool) $service->delete();
    }
}
