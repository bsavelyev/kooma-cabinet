<?php

namespace app\repositories;

use app\models\forms\RbacForm;
use Yii;

class RbacRepository
{
    /**
     * @return array[]
     */
    public function findPermissionsWithParents()
    {
        return RbacForm::find()
            ->select(['auth_item.name', 'kooma.auth_item_child.parent'])
            ->innerJoin(
                'kooma.auth_item_child',
                'kooma.auth_item_child.child = kooma.auth_item.name'
            )
            ->where(['type' => 2])
            ->asArray()
            ->all();
    }

    /**
     * @param string $roleName
     * @return array{permissions: array<string, string>, selected: string[]}
     */
    public function getPermissionOptionsForRole($roleName)
    {
        $permissions = [];
        $selected = [];

        foreach ($this->findPermissionsWithParents() as $row) {
            $permissions[$row['name']] = $row['name'];
            if ($row['parent'] === $roleName) {
                $selected[] = $row['name'];
            }
        }

        return [
            'permissions' => $permissions,
            'selected' => $selected,
        ];
    }

    /**
     * @param string $roleName
     * @param string[] $permissionNames
     */
    public function syncRolePermissions($roleName, array $permissionNames)
    {
        Yii::$app->db->transaction(function () use ($roleName, $permissionNames) {
            Yii::$app->db->createCommand()
                ->delete('kooma.auth_item_child', ['parent' => $roleName])
                ->execute();

            if ($permissionNames === []) {
                return;
            }

            $rows = [];
            foreach ($permissionNames as $permissionName) {
                $rows[] = [$roleName, $permissionName];
            }

            Yii::$app->db->createCommand()->batchInsert(
                'kooma.auth_item_child',
                ['parent', 'child'],
                $rows
            )->execute();
        });
    }
}
