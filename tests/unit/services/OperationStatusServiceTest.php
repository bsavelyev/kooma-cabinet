<?php

namespace tests\unit\services;

use app\repositories\OperationRepository;
use app\services\OperationStatusService;
use Codeception\Test\Unit;

class OperationStatusServiceTest extends Unit
{
    public function testChangeBulkOperationNotFound()
    {
        $repository = $this->createMock(OperationRepository::class);
        $repository->method('findByExtId')->willReturn(null);

        $service = new OperationStatusService($repository);
        $result = $service->changeBulk(['ext-missing']);

        verify($result)->equals(['ext-missing - Операция не найдена']);
    }

    public function testChangeBulkAmountBelowMinimum()
    {
        $operation = $this->makeOperation('100', 100);

        $repository = $this->createMock(OperationRepository::class);
        $repository->method('findByExtId')->willReturn($operation);

        $service = new OperationStatusService($repository);
        $result = $service->changeBulk(['ext-low']);

        verify($result)->equals(['ext-low - Сумма меньше 150']);
    }

    public function testChangeBulkSuccess()
    {
        $operation = $this->makeOperation('100', 200);

        $repository = $this->createMock(OperationRepository::class);
        $repository->method('findByExtId')->willReturn($operation);
        $repository->method('changeStatus')->with(100)->willReturn(1);

        $service = new OperationStatusService($repository);
        $result = $service->changeBulk(['ext-ok']);

        verify($result)->equals(['ext-ok - Success']);
    }

    public function testChangeBulkRepositoryThrows()
    {
        $operation = $this->makeOperation('100', 200);

        $repository = $this->createMock(OperationRepository::class);
        $repository->method('findByExtId')->willReturn($operation);
        $repository->method('changeStatus')->willThrowException(new \RuntimeException('db error'));

        $service = new OperationStatusService($repository);
        $result = $service->changeBulk(['ext-fail']);

        verify($result)->equals(['ext-fail - Ошибка']);
    }

    /**
     * @param string $id
     * @param float $amount
     * @return object
     */
    private function makeOperation($id, $amount)
    {
        return (object) [
            'id' => $id,
            'amount' => $amount,
        ];
    }
}
