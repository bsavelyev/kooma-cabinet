<?php

namespace app\controllers;

use app\models\LimitsPaymentAccountModel;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

class ModeratorLimitsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['moderator'],
                    ],
                ],
            ]
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => LimitsPaymentAccountModel::find()
                ->joinWith(['service'])
                ->joinWith(['limitPeriod'])
                ->joinWith(['limitValueType'])
                ->orderBy(['id' => SORT_DESC]),
            'sort' => false,
        ]);
        return $this->render('/moderator/limits/index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new LimitsPaymentAccountModel();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $model->save();
                Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'success'));
                return $this->redirect(['moderator-limits/index']);
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->errorInfo[2]);
                return $this->redirect(['moderator-limits/index']);
            }
        }

        return $this->renderAjax('/moderator/limits/_form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = LimitsPaymentAccountModel::findOne(['id' => $id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'success'));
            return $this->redirect(['moderator-limits/index']);
        }

        return $this->renderAjax('/moderator/limits/_formUpdate', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        $model = LimitsPaymentAccountModel::findOne(['id' => $id]);
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'delete_success'));
            return $this->redirect(['moderator-limits/index']);
        }

        return $this->renderAjax('/moderator-limits', ['model' => $model]);
    }
}
