<?php

namespace app\controllers;

use app\models\LimitsPaymentAccountByMerchantModel;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

class ModeratorLimitsByMerchantController extends Controller
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
            'query' => LimitsPaymentAccountByMerchantModel::find()
                ->joinWith(['subject'])
                ->joinWith(['limitPeriod'])
                ->joinWith(['limitValueType'])
                ->orderBy(['id' => SORT_DESC]),
            'sort' => false,
        ]);
        return $this->render('/moderator/merchant/index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new LimitsPaymentAccountByMerchantModel();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $model->save();
                Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'success'));
                return $this->redirect(['moderator-limits-by-merchant/index']);
            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->errorInfo[2]);
                return $this->redirect(['moderator-limits-by-merchant/index']);
            }
        }

        return $this->renderAjax('/moderator/merchant/_form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = LimitsPaymentAccountByMerchantModel::findOne(['id' => $id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'success'));
            return $this->redirect(['moderator-limits-by-merchant/index']);
        }

        return $this->renderAjax('/moderator/merchant/_formUpdate', ['model' => $model]);
    }

    public function actionDelete(int $id)
    {
        $model = LimitsPaymentAccountByMerchantModel::findOne(['id' => $id]);
        if ($model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('manual/cabinet', 'delete_success'));
            return $this->redirect(['moderator-limits-by-merchant/index']);
        }

        return $this->renderAjax('/moderator-limits', ['model' => $model]);
    }
}
