<?php

namespace app\controllers;

use app\models\FeesModel;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class ModeratorFeesController extends Controller
{
    public function behaviors(): array
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
            ],
        ];
    }

    public function actionIndex(): string
    {
        $model = new FeesModel();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);

        return $this->render('/moderator/fees/index', [
            'dataProvider' => $dataProvider,
            'model' => $model
        ]);
    }

    public function actionCreate()
    {
        $model = new FeesModel();
        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->changeFees($model);
                \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
                return $this->redirect(['moderator-fees/index']);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', \Yii::t('manual/cabinet', 'db_error'));
                return $this->redirect(['moderator-fees/index']);
            }
        }
        return $this->renderAjax('/moderator/fees/_form', ['model' => $model]);
    }

    public function actionUpdate(int $id)
    {
        $model = FeesModel::findOne(['id' => $id]);
        if ($model->load(\Yii::$app->request->post()) && $model->validate() && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
            return $this->redirect(['moderator-fees/index']);
        }
        return $this->renderAjax('/moderator/fees/_formUpdate', ['model' => $model]);
    }

    private function changeFees(FeesModel $model): void
    {
        try {
            \Yii::$app->db->createCommand(
                'SELECT * FROM cabinet.create_fee(:service_id, :provider_id, :value, :value_type, :start_date, :type_id, :agent_id, :end_date, :min_value, :max_value)', [
                    ':service_id' => $model->service_id,
                    ':provider_id' => $model->provider_id,
                    ':value' => $model->value,
                    ':type_id' => $model->type_id,
                    ':value_type' => $model->value_type,
                    ':start_date' => $model->start_date,
                    ':agent_id' => $model->agent_id,
                    ':end_date' => $model->end_date,
                    ':min_value' => $model->min_value,
                    ':max_value' => $model->max_value,
                ]
            )->queryScalar();
        } catch (Exception $exception) {
        }
    }
}
