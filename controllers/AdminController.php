<?php

namespace app\controllers;

use app\models\AssignmentModel;
use app\models\forms\PermissionForm;
use app\models\forms\RbacForm;
use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;

class AdminController extends Controller
{
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
            ]
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
        $model = AssignmentModel::findOne(['user_id' => $id]);
        if (Yii::$app->request->post()) {
            $model = new AssignmentModel();
            $model->load(Yii::$app->request->post());
            $model->setAttribute('user_id', $id);
            $model->validate();
            $model->save();
            $this->setSuccessFlash();
            return $this->redirect(['index']);
        }
        $model = new AssignmentModel();
        return $this->renderAjax('subject/_changeRoles', [
            'model' => $model,
            'id' => $id
        ]);
    }

    public function actionRbac()
    {
        $model = new RbacForm();
        $dataProvider = new ActiveDataProvider([
            'query' => RbacForm::find()->where(['type' => 1])->orderBy('name'),
            'sort' => false,
        ]);
        return $this->render('rbac/index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionCreateRbac()
    {
        $rbac = new RbacForm();
        if ($rbac->load(Yii::$app->request->post()) && $rbac->save()) {
            $this->setSuccessFlash();
            return $this->redirect(['admin/rbac']);
        }
        return $this->renderAjax('rbac/_form', ['model' => $rbac]);
    }

    public function actionUpdateRbac($name)
    {
        $model = RbacForm::findOne(['name' => $name]);
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
        $model = new PermissionForm();
        $data = RbacForm::find()
            ->select(['auth_item.name', 'kooma.auth_item_child.parent'])
            ->innerJoin('kooma.auth_item_child', 'kooma.auth_item_child.child = kooma.auth_item.name')
            ->where(['type' => 2])
            ->asArray()
            ->all();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $permissions = array_map(fn ($permission) => [$name, $permission], $model->child);
            $model->multipleSave($permissions);
            $this->setSuccessFlash();
            return $this->redirect(['admin/rbac']);
        }

        $selected = [];
        $permissions = [];
        foreach ($data as $permission) {
            $permissions[$permission['name']] = $permission['name'];
            if ($permission['parent'] === $name) {
                $selected[] = $permission['name'];
            }
        }

        $model->child = $selected;

        return $this->renderAjax('rbac/_permissions', [
            'model' => $model,
            'permissions' => $permissions,
        ]);
    }

    protected function setSuccessFlash()
    {
        Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'success'));
    }
}