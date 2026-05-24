<?php

namespace app\controllers;

use app\models\AssignmentModel;
use app\models\AuthItemModel;
use app\models\forms\PermissionForm;
use app\models\User;
use app\services\RbacService;
use app\services\RoleAssignmentService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;

class AdminController extends Controller
{
    /** @var RbacService */
    private $rbacService;

    /** @var RoleAssignmentService */
    private $roleAssignmentService;

    public function __construct(
        $id,
        $module,
        $config,
        RbacService $rbacService,
        RoleAssignmentService $roleAssignmentService
    ) {
        $this->rbacService = $rbacService;
        $this->roleAssignmentService = $roleAssignmentService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['administrator'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new User();
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->orderBy(['id' => SORT_DESC]),
            'sort' => false,
        ]);

        return $this->render('subject/index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionCreateSubject()
    {
        $user = new User();
        if ($user->load(Yii::$app->request->post()) && $user->save()) {
            $this->setSuccessFlash();

            return $this->redirect(['index']);
        }

        return $this->renderAjax('subject/_form', ['model' => $user]);
    }

    public function actionUpdateSubject($id)
    {
        $model = User::findOne(['id' => $id]);
        $model->setScenario(User::SCENARIO_UPDATE);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->setSuccessFlash();

            return $this->redirect(['index']);
        }

        return $this->renderAjax('subject/_formUpdate', ['model' => $model]);
    }

    public function actionChangePassword($id)
    {
        $model = User::findOne(['id' => $id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->setSuccessFlash();

            return $this->redirect(['subject/index']);
        }

        return $this->renderAjax('subject/_changePassword', ['model' => $model]);
    }

    /**
     * @param int $id
     * @return string|Response
     */
    public function actionChangeRoles($id)
    {
        if (Yii::$app->request->post()) {
            $model = new AssignmentModel();
            $model->load(Yii::$app->request->post());
            $this->roleAssignmentService->assignRole((int) $id, $model->item_name);
            $this->setSuccessFlash();

            return $this->redirect(['index']);
        }

        $model = new AssignmentModel();

        return $this->renderAjax('subject/_changeRoles', [
            'model' => $model,
            'id' => $id,
        ]);
    }

    public function actionRbac()
    {
        $model = new AuthItemModel();
        $dataProvider = new ActiveDataProvider([
            'query' => $this->rbacService->getRolesQuery(),
            'sort' => false,
        ]);

        return $this->render('rbac/index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionCreateRbac()
    {
        $model = new AuthItemModel();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->setSuccessFlash();

            return $this->redirect(['admin/rbac']);
        }

        return $this->renderAjax('rbac/_form', ['model' => $model]);
    }

    public function actionUpdateRbac($name)
    {
        $model = AuthItemModel::findOne(['name' => $name]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->setSuccessFlash();

            return $this->redirect(['admin/rbac']);
        }

        return $this->renderAjax('rbac/_form', ['model' => $model]);
    }

    /**
     * @param string $name
     * @return string|Response
     */
    public function actionSetPermissions($name)
    {
        $permissionData = $this->rbacService->buildPermissionForm($name);
        /** @var PermissionForm $model */
        $model = $permissionData['form'];

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $children = $model->child;
            $this->rbacService->saveRolePermissions($name, $children);
            $this->setSuccessFlash();

            return $this->redirect(['admin/rbac']);
        }

        return $this->renderAjax('rbac/_permissions', [
            'model' => $model,
            'permissions' => $permissionData['permissions'],
        ]);
    }

    protected function setSuccessFlash()
    {
        Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'success'));
    }
}
