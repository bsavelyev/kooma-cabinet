<?php

namespace app\services;

use app\repositories\ServiceProviderRepository;
use RuntimeException;
use Yii;

class ServiceProviderService
{
    /** @var ServiceProviderRepository */
    private $repository;

    public function __construct(ServiceProviderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $id
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function delete($id)
    {
        Yii::$app->db->transaction(function () use ($id) {
            if (!$this->repository->deleteWithDependencies($id)) {
                throw new RuntimeException('Service provider not found');
            }
        });
    }
}
