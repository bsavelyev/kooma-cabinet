<?php

namespace app\services;

use app\models\providers\Wooppay;
use app\repositories\ServiceImportRepository;
use Yii;

class WooppayImportService
{
    /** @var ServiceImportRepository */
    private $repository;

    /** @var ServiceFieldSetupService */
    private $fieldSetupService;

    /** @var ServiceImportMessageFormatter */
    private $messageFormatter;

    public function __construct(
        ServiceImportRepository $repository,
        ServiceFieldSetupService $fieldSetupService,
        ServiceImportMessageFormatter $messageFormatter
    ) {
        $this->repository = $repository;
        $this->fieldSetupService = $fieldSetupService;
        $this->messageFormatter = $messageFormatter;
    }

    /**
     * @param string $servicesIds comma-separated
     * @param int $userId
     * @return string[]
     */
    public function import($servicesIds, $userId)
    {
        $ids = array_filter(array_map('trim', explode(',', $servicesIds)));
        $services = Wooppay::getServices($ids);
        $result = [];

        foreach ($services as $serviceW) {
            if ($this->repository->serviceExists($serviceW['name'])) {
                $result[] = [
                    'system_name' => $serviceW['name'],
                    'user_name' => $serviceW['title'],
                    'status' => 'exists',
                ];
                continue;
            }

            Yii::$app->db->transaction(function () use ($serviceW, $userId, &$result) {
                $date = $this->repository->getDbTimestamp();

                $service = $this->repository->createService(
                    $serviceW['name'],
                    $serviceW['title'],
                    $date
                );

                $provider = $this->repository->createProvider(
                    $service->id,
                    $userId,
                    $date,
                    $serviceW['name']
                );

                $this->fieldSetupService->setupWooppayFields($provider, $serviceW['fields']);

                $result[] = [
                    'service_id' => $service->id,
                    'system_name' => $service->system_name,
                    'user_name' => $service->user_name,
                    'status' => 'created',
                ];
            });
        }

        return $this->messageFormatter->format($result);
    }
}
