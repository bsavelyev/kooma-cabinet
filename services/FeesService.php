<?php

namespace app\services;

use app\models\FeesModel;
use app\repositories\FeesRepository;
use yii\db\Exception;

class FeesService
{
    /** @var FeesRepository */
    private $repository;

    public function __construct(FeesRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param FeesModel $model
     * @throws Exception
     */
    public function create(FeesModel $model)
    {
        $this->repository->createFee($model);
    }
}
