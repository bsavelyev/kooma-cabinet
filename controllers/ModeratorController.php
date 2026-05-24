<?php

namespace app\controllers;

use app\models\forms\ServiceGrabberForm;
use app\models\forms\ServiceGrabberQiwiForm;
use app\models\ServiceModel;
use app\services\QiwiImportService;
use app\services\WooppayImportService;
use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderFieldsValidationModel;
use app\models\ServiceProviderModel;
use app\repositories\ServiceProviderFieldsRepository;
use app\services\ServiceCatalogService;
use app\services\ServiceProviderService;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

class ModeratorController extends Controller
{
    /** @var ServiceProviderService */
    private $serviceProviderService;
    /** @var ServiceCatalogService */
    private $serviceCatalogService;
    /** @var ServiceProviderFieldsRepository */
    private $fieldsRepository;
    /** @var WooppayImportService */
    private $wooppayImportService;
    /** @var QiwiImportService */
    private $qiwiImportService;

    public function __construct(
        $id,
        $module,
        $config,
        ServiceProviderService $serviceProviderService,
        ServiceCatalogService $serviceCatalogService,
        ServiceProviderFieldsRepository $fieldsRepository,
        WooppayImportService $wooppayImportService,
        QiwiImportService $qiwiImportService
    ) {
        $this->serviceProviderService = $serviceProviderService;
        $this->serviceCatalogService = $serviceCatalogService;
        $this->fieldsRepository = $fieldsRepository;
        $this->wooppayImportService = $wooppayImportService;
        $this->qiwiImportService = $qiwiImportService;
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
        $searchModel = new ServiceModel();
        $dataProvider = new ActiveDataProvider([
            'query' => ServiceModel::find(),
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
        ]);

        // Применяем фильтры из GET-параметров
        if ($searchModel->load(\Yii::$app->request->get()) && $searchModel->validate()) {
            $dataProvider->query->andFilterWhere(['id' => $searchModel->id]);
            $dataProvider->query->andFilterWhere(['like', 'lower(system_name)', strtolower($searchModel->system_name)]);
            $dataProvider->query->andFilterWhere(['like', 'lower(user_name)', strtolower($searchModel->user_name)]);
            $dataProvider->query->andFilterWhere(['status' => $searchModel->status]);
        }

        return $this->render('service/index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionServiceGrabber()
    {
        $model = new ServiceGrabberForm();
        if (!\Yii::$app->request->post()) {
            return $this->renderAjax('service/_formGrabber', ['model' => $model]);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $serviceNames = $this->wooppayImportService->import($model->services_ids, (int) $model->user_id);
                \Yii::$app->session->setFlash('success', implode('<br>', $serviceNames));

                return $this->redirect(['moderator/index']);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage());

                return $this->redirect(['moderator/index']);
            }
        }

        return $this->renderAjax('moderator/index', ['model' => $model]);
    }

    public function actionServiceGrabberQiwi()
    {
        $model = new ServiceGrabberQiwiForm();
        if (!\Yii::$app->request->post()) {
            return $this->renderAjax('service/_formGrabberQiwi', ['model' => $model]);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $model->file = UploadedFile::getInstance($model, 'file');
                if ($model->validate()) {
                    $filePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$model->file->baseName.'.'.$model->file->extension;
                    $model->file->saveAs($filePath);

                    try {
                        $serviceNames = $this->qiwiImportService->importFromFile((int) $model->user_id, $filePath);
                        \Yii::$app->session->setFlash('success', implode('<br>', $serviceNames));

                        return $this->redirect(['moderator/index']);
                    } catch (\Exception $e) {
                        \Yii::$app->session->setFlash('error', 'Ошибка при парсинге файла: '.$e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage());

                return $this->redirect(['moderator/index']);
            }
        }

        return $this->renderAjax('moderator/index', ['model' => $model]);
    }

    public function actionServiceProvider()
    {
        $model = new ServiceProviderModel();
        $dataProvider = $model->search(\Yii::$app->request->queryParams);

        return $this->render('serviceProvider/index', ['dataProvider' => $dataProvider, 'model' => $model]);
    }

    public function actionCreateServiceProvider()
    {
        $model = new ServiceProviderModel();
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/service-provider']);
        }

        return $this->renderAjax('serviceProvider/_form', ['model' => $model]);
    }

    public function actionDeleteServiceProvider(int $id)
    {
        try {
            $this->serviceProviderService->delete($id);
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
        } catch (\Throwable $exception) {
            \Yii::$app->session->setFlash('error', $exception->getMessage());
        }

        return $this->redirect(['moderator/service-provider']);
    }

    public function actionUpdateServiceProvider($id)
    {
        $model = ServiceProviderModel::findOne(['id' => $id]);
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/service-provider']);
        }

        return $this->renderAjax('serviceProvider/_form', ['model' => $model]);
    }

    public function actionCreateService()
    {
        $model = new ServiceModel();
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/index']);
        }

        return $this->renderAjax('service/_form', ['model' => $model]);
    }

    public function actionUpdateService($id)
    {
        $model = ServiceModel::findOne(['id' => $id]);
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/index']);
        }

        return $this->renderAjax('service/_form', ['model' => $model]);
    }

    public function actionDeleteService($id)
    {
        try {
            $this->serviceCatalogService->delete((int) $id);
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));
        } catch (\Throwable $exception) {
            \Yii::$app->session->setFlash('error', \Yii::t('manual/cabinet', $exception->getMessage()));
        }

        return $this->redirect(['moderator/index']);
    }

    public function actionFields($id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->fieldsRepository->getFieldsQueryByProviderId((int) $id),
        ]);

        return $this->render('service/Fields', ['dataProvider' => $dataProvider, 'id' => $id]);
    }

    public function actionCreateServiceFields(string $id)
    {
        $model = new ServiceProviderFieldsModel();
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/fields?id='.$id]);
        }

        return $this->renderAjax('service/_formUpdateFields', ['model' => $model, 'service_id' => $id]);
    }

    public function actionUpdateServiceFields($id)
    {
        $model = ServiceProviderFieldsModel::findOne(['id' => $id]);
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/fields?id='.$model->service_provider_id]);
        }

        return $this->renderAjax('service/_formUpdateFields', ['model' => $model, 'service_id' => $id]);
    }

    public function actionFieldsValidation($id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ServiceProviderFieldsValidationModel::find()
                ->where(['service_provider_field_id' => $id])
                ->orderBy('id'),
            'sort' => false,
        ]);

        return $this->render('service/FieldsValidation', ['dataProvider' => $dataProvider, 'id' => $id]);
    }

    public function actionCreateServiceFieldsValidation($id)
    {
        $model = new ServiceProviderFieldsValidationModel();
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $postData = $request->post('ServiceProviderFieldsValidationModel');
            $model->setAttributes([
                'service_provider_field_id' => $postData['service_provider_field_id'],
                'param' => json_decode($postData['param'], true),
                'type' => $postData['type'],
                'descr' => $postData['descr'],
            ]);
            if ($model->save()) {
                \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

                return $this->redirect(['moderator/fields-validation', 'id' => $model->service_provider_field_id]);
            }
        }

        return $this->renderAjax('service/_formUpdateFieldsValidation', ['model' => $model, 'id' => $id]);
    }

    public function actionUpdateServiceFieldsValidation($id)
    {
        $model = ServiceProviderFieldsValidationModel::findOne(['service_provider_field_id' => $id]);
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $postData = $request->post('ServiceProviderFieldsValidationModel');
            $model->setAttributes([
                'service_provider_field_id' => $postData['service_provider_field_id'],
                'param' => json_decode($postData['param'], true),
                'type' => $postData['type'],
                'descr' => $postData['descr'],
            ]);
            if ($model->save()) {
                \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

                return $this->redirect(['moderator/fields-validation', 'id' => $model->service_provider_field_id, 'service_id' => $id]);
            }
        }

        return $this->renderAjax('service/_formUpdateFieldsValidation', ['model' => $model, 'id' => $id]);
    }
}
