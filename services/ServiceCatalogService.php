<?php

namespace app\services;

use app\repositories\ServiceRepository;
use RuntimeException;
use Yii;

class ServiceCatalogService
{
    /** @var ServiceRepository */
    private $repository;

    public function __construct(ServiceRepository $repository)
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
                throw new RuntimeException('Service not found');
            }
        });
    }
}
