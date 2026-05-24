<?php

namespace app\services;

use app\repositories\OperationRepository;

class OperationStatusService
{
    const MIN_AMOUNT = 150;

    /** @var OperationRepository */
    private $operationRepository;

    public function __construct(OperationRepository $operationRepository)
    {
        $this->operationRepository = $operationRepository;
    }

    /**
     * @param string[] $extIds
     * @return string[]
     */
    public function changeBulk(array $extIds)
    {
        $result = [];

        foreach ($extIds as $extId) {
            $operation = $this->operationRepository->findByExtId($extId);
            if ($operation === null) {
                $result[] = sprintf('%s - %s', $extId, 'Операция не найдена');
                continue;
            }
            if ($operation->amount < self::MIN_AMOUNT) {
                $result[] = sprintf('%s - %s', $extId, 'Сумма меньше 150');
                continue;
            }
            try {
                $status = $this->operationRepository->changeStatus($operation->id);
                $result[] = sprintf('%s - %s', $extId, $status > 0 ? 'Success' : $status);
            } catch (\Throwable $e) {
                $result[] = sprintf('%s - %s', $extId, 'Ошибка');
            }
        }

        return $result;
    }
}
