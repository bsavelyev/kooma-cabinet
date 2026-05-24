<?php

namespace app\controllers;

use app\models\Account;
use app\models\forms\OperationForm;
use app\models\OperationModel;
use app\models\OperationStatusHistoryModel;
use app\services\ExportService;
use app\services\FinancierService;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\BadRequestHttpException;

class FinancierController extends Controller
{
    public const REPLENISHMENT = 'replenishment';
    public const DISCHARGE = 'discharge';

    private FinancierService $service;
    private ExportService $exportService;

    public function __construct($id, $module, $config, FinancierService $financierService, ExportService $exportService)
    {
        $this->service = $financierService;
        $this->exportService = $exportService;
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
                        'roles' => ['financier'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionOperation()
    {
        $model = new OperationForm();
        $query = OperationModel::getQuery();

        \Yii::$app->getHomeUrl();
        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                $query = (new OperationModel())->search($model->attributes);
            }
        }

        $dataProvider = $this->dataProvider($query);

        return $this->render('operation/index', [
            'dataProvider' => $dataProvider,
            'historyForm' => $model,
        ]);
    }

    public function actionHistory()
    {
        $model = new OperationStatusHistoryModel();
        $query = OperationStatusHistoryModel::find();

        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post('OperationStatusHistoryModel', []);
            if ($model->load(['OperationStatusHistoryModel' => $post]) && $model->validate()) {
                $query = $model->search($post);
            }
        }

        $page = (int)\Yii::$app->request->post('page', 1);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => max($page - 1, 0),
                'pageSize' => 20,
            ],
        ]);

        return $this->render('operations_status_history/index', [
            'dataProvider' => $dataProvider,
            'historyForm' => $model,
        ]);
    }

    public function actionChangeStatusOfSeveralOperations()
    {
        $model = new DynamicModel(['operations_ids']);
        $model->addRule(['operations_ids'], 'required');
        $model->addRule(['operations_ids'], 'string');

        if (!\Yii::$app->request->isPost) {
            return $this->renderAjax('operation/_change', ['model' => $model]);
        }

        $model->load(\Yii::$app->request->post());

        if (!$model->validate()) {
            \Yii::$app->session->setFlash('error', 'Некорректные данные');
            return $this->redirect('operation?sort=-id');
        }

        $operations_ids = array_filter(array_map('trim', explode(',', $model->operations_ids)));
        $result = [];

        foreach ($operations_ids as $oper) {
            $operation = OperationModel::find()->where(['ext_id' => $oper])->one();
            if (!$operation) {
                $result[] = sprintf('%s - %s', $oper, 'Операция не найдена');
                continue;
            }
            if ($operation->amount < 150) {
                $result[] = sprintf('%s - %s', $oper, 'Сумма меньше 150');
                continue;
            }
            try {
                $status = $operation->changeStatus($operation->id);
                $result[] = sprintf('%s - %s', $oper, $status > 0 ? 'Success' : $status);
            } catch (\Throwable $e) {
                $result[] = sprintf('%s - %s', $oper, 'Ошибка');
            }
        }

        \Yii::$app->session->setFlash('success', implode('<br>', $result));
        return $this->redirect('operation?sort=-id');
    }

    public function actionChangeStatus($id)
    {
        $id = (int)$id;
        $operation = OperationModel::findOne(['id' => $id]);
        if (!$operation) {
            throw new BadRequestHttpException('Операция не найдена');
        }

        try {
            $operation->changeStatus($id);
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', 'error');
        }

        $query = OperationModel::getQuery();
        $page = (int)\Yii::$app->request->post('page', 1);

        $dataProvider = new ActiveDataProvider([
            'query' => $query->asArray(),
            'pagination' => [
                'page' => max($page - 1, 0),
                'pageSize' => 20,
            ],
        ]);

        return $this->redirect(['financier/operation', 'sort' => '-id']);
    }

    public function actionAgents()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Account::find()
                ->joinWith(['subject'])
                ->orderBy('account.id'),
            'sort' => false,
        ]);

        return $this->render('agents/index', ['dataProvider' => $dataProvider]);
    }

    public function actionEmission($id, $type)
    {
        $model = new OperationModel();
        if (\Yii::$app->request->isPost) {
            $data = \Yii::$app->request->post();
            if ($model->load($data) && $model->validate()) {
                try {
                    $this->service->createOperation($data, (int)$id, OperationModel::TYPE_CASHIN, self::REPLENISHMENT);
                    \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
                    return $this->redirect(['financier/agents']);
                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', 'error');
                    return $this->redirect(['financier/agents']);
                }
            }
        }
        return $this->renderAjax('/financier/agents/_form', ['model' => $model]);
    }

    public function actionRedemption($id, $type)
    {
        $model = new OperationModel();
        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $data = \Yii::$app->request->post();
                $this->service->createOperation($data, (int)$id, OperationModel::TYPE_CASHOUT, self::DISCHARGE);
                \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
                return $this->redirect(['financier/agents']);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', 'error');
                return $this->redirect(['financier/agents']);
            }
        }
        return $this->renderAjax('/financier/agents/_form', ['model' => $model]);
    }

    public function actionExport(string $format)
    {
        $model = new OperationForm();
        $query = OperationModel::getQuery();

        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $this->exportService->actionExport($format, $dataProvider);
        \Yii::$app->response->send();

        return $this->redirect(['financier/operation?sort=-id']);
    }

    private function dataProvider(Query $query): ActiveDataProvider
    {
        $page = (int)\Yii::$app->request->post('page', 1);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => max($page - 1, 0),
                'pageSize' => 20,
            ],
        ]);
    }
}
