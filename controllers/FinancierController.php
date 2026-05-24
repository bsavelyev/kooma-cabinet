<?php

namespace app\controllers;

use app\models\Account;
use app\models\forms\ChangeOperationsStatusForm;
use app\models\forms\OperationForm;
use app\models\OperationModel;
use app\models\OperationStatusHistoryModel;
use app\models\search\OperationSearch;
use app\models\search\OperationStatusHistorySearch;
use app\repositories\OperationRepository;
use app\services\ExportService;
use app\services\FinancierService;
use app\services\OperationStatusService;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class FinancierController extends Controller
{
    public const REPLENISHMENT = 'replenishment';
    public const DISCHARGE = 'discharge';

    /** @var FinancierService */
    private $service;

    /** @var ExportService */
    private $exportService;

    /** @var OperationStatusService */
    private $operationStatusService;

    /** @var OperationRepository */
    private $operationRepository;

    /** @var OperationSearch */
    private $operationSearch;

    /** @var OperationStatusHistorySearch */
    private $historySearch;

    public function __construct(
        $id,
        $module,
        $config,
        FinancierService $financierService,
        ExportService $exportService,
        OperationStatusService $operationStatusService,
        OperationRepository $operationRepository,
        OperationSearch $operationSearch,
        OperationStatusHistorySearch $historySearch
    ) {
        $this->service = $financierService;
        $this->exportService = $exportService;
        $this->operationStatusService = $operationStatusService;
        $this->operationRepository = $operationRepository;
        $this->operationSearch = $operationSearch;
        $this->historySearch = $historySearch;
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
        $query = $this->operationSearch->baseQuery();

        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                $query = $this->operationSearch->buildQuery($model->attributes);
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
                $query = $this->historySearch->buildQuery($post);
            }
        }

        $page = (int) \Yii::$app->request->post('page', 1);

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
        $model = new ChangeOperationsStatusForm();

        if (!\Yii::$app->request->isPost) {
            return $this->renderAjax('operation/_change', ['model' => $model]);
        }

        $model->load(\Yii::$app->request->post());

        if (!$model->validate()) {
            \Yii::$app->session->setFlash('error', 'Некорректные данные');

            return $this->redirect('operation?sort=-id');
        }

        $operationsIds = array_filter(array_map('trim', explode(',', $model->operations_ids)));
        $result = $this->operationStatusService->changeBulk($operationsIds);

        \Yii::$app->session->setFlash('success', implode('<br>', $result));

        return $this->redirect('operation?sort=-id');
    }

    public function actionChangeStatus($id)
    {
        $id = (int) $id;
        $operation = $this->operationRepository->findById($id);
        if ($operation === null) {
            throw new BadRequestHttpException('Операция не найдена');
        }

        try {
            $this->operationRepository->changeStatus($id);
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', 'error');
        }

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
                    $this->service->createOperation($data, (int) $id, OperationModel::TYPE_CASHIN, self::REPLENISHMENT);
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
                $this->service->createOperation($data, (int) $id, OperationModel::TYPE_CASHOUT, self::DISCHARGE);
                \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

                return $this->redirect(['financier/agents']);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', 'error');

                return $this->redirect(['financier/agents']);
            }
        }

        return $this->renderAjax('/financier/agents/_form', ['model' => $model]);
    }

    public function actionExport($format)
    {
        $model = new OperationForm();
        $query = $this->operationSearch->baseQuery();

        if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
            $query = $this->operationSearch->buildQuery($model->attributes);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $this->exportService->actionExport($format, $dataProvider);
        \Yii::$app->response->send();

        return $this->redirect(['financier/operation?sort=-id']);
    }

    /**
     * @param Query $query
     * @return ActiveDataProvider
     */
    private function dataProvider($query)
    {
        $page = (int) \Yii::$app->request->post('page', 1);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' => max($page - 1, 0),
                'pageSize' => 20,
            ],
        ]);
    }
}
