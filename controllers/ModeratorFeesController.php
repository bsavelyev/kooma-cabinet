<?php

namespace app\controllers;

use app\models\FeesModel;
use app\services\FeesService;
use Exception;
use yii\filters\AccessControl;
use yii\web\Controller;

class ModeratorFeesController extends Controller
{
    /** @var FeesService */
    private $feesService;

    public function __construct($id, $module, $config, FeesService $feesService)
    {
        $this->feesService = $feesService;
        parent::__construct($id, $module, $config);
    }

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
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new FeesModel();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);

        return $this->render('/moderator/fees/index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new FeesModel();
        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $this->feesService->create($model);
                \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

                return $this->redirect(['moderator-fees/index']);
            } catch (Exception $e) {
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
}
