<?php

namespace app\services;

use app\repositories\AssignmentRepository;

class RoleAssignmentService
{
    /** @var AssignmentRepository */
    private $repository;

    public function __construct(AssignmentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $userId
     * @param string $itemName
     * @return bool
     */
    public function assignRole($userId, $itemName)
    {
        return $this->repository->assignRole($userId, $itemName);
    }
}
