<?php

namespace app\services;

use app\models\forms\PermissionForm;
use app\repositories\RbacRepository;

class RbacService
{
    /** @var RbacRepository */
    private $repository;

    public function __construct(RbacRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRolesQuery()
    {
        return $this->repository->findRolesQuery();
    }

    /**
     * @param string $roleName
     * @return array{form: PermissionForm, permissions: array<string, string>}
     */
    public function buildPermissionForm($roleName)
    {
        $data = $this->repository->getPermissionOptionsForRole($roleName);

        $form = new PermissionForm();
        $form->child = $data['selected'];

        return [
            'form' => $form,
            'permissions' => $data['permissions'],
        ];
    }

    /**
     * @param string $roleName
     * @param string[] $permissionNames
     */
    public function saveRolePermissions($roleName, array $permissionNames)
    {
        $this->repository->syncRolePermissions($roleName, $permissionNames);
    }
}
