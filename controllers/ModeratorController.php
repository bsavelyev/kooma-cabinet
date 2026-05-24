<?php

namespace app\controllers;

use app\helpers\ExcelParser;
use app\helpers\QiwiServiceSaver;
use app\helpers\ServiceSaver;
use app\models\FeesModel;
use app\models\ServiceModel;
use app\models\ServiceProviderFieldsModel;
use app\models\ServiceProviderFieldsValidationModel;
use app\models\ServiceProviderModel;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

class ModeratorController extends Controller
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
        $model = new DynamicModel();
        $model->addRule(['services_ids', 'user_id'], 'required');
        $model->addRule(['services_ids'], 'string');
        $model->addRule(['user_id'], 'integer');
        if (!\Yii::$app->request->post()) {
            return $this->renderAjax('service/_formGrabber', ['model' => $model]);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $serviceNames = ServiceSaver::saveService($model->services_ids, $model->user_id);
                \Yii::$app->session->setFlash('success', implode('<br>', $serviceNames));

                return $this->redirect(['moderator/index']);
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage() ?? 'error');

                return $this->redirect(['moderator/index']);
            }
        }

        return $this->renderAjax('moderator/index', ['model' => $model]);
    }

    public function actionServiceGrabberQiwi()
    {
        $model = new DynamicModel(['file']);
        $model->addRule(['user_id'], 'integer');
        $model->addRule(['file'], 'file', ['extensions' => 'xlsx']);
        if (!\Yii::$app->request->post()) {
            return $this->renderAjax('service/_formGrabberQiwi', ['model' => $model]);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            try {
                $model->file = UploadedFile::getInstance($model, 'file');
                if ($model->validate()) {
                    $filePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$model->file->baseName.'.'.$model->file->extension;
                    $model->file->saveAs($filePath);

                    $parser = new ExcelParser();

                    try {
                        //                        $parsedData = $parser->parseExcel($filePath);
                        $serviceNames = QiwiServiceSaver::saveService($model->user_id, $filePath);
                        \Yii::$app->session->setFlash('success', implode('<br>', $serviceNames));

                        return $this->redirect(['moderator/index']);
                    } catch (\Exception $e) {
                        if ($filePath && file_exists($filePath)) {
                            unlink($filePath);
                        }

                        \Yii::$app->session->setFlash('error', 'Ошибка при парсинге файла: '.$e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Yii::$app->session->setFlash('error', $e->getMessage() ?? 'error');

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
        $providerModel = new ServiceProviderModel();
        $providerModel = $providerModel->findOne(['id' => $id]);

        $providerModelFields = new ServiceProviderFieldsModel();
        $providerModelFields = $providerModelFields->findAll(['service_provider_id' => $providerModel->id ?? null]);

        foreach ($providerModelFields as $field) {
            $validation = new ServiceProviderFieldsValidationModel();
            $validation = $validation->findAll(['service_provider_field_id' => $field->id ?? null]);

            if (!empty($validation[0]->attributes['id'])) {
                ServiceProviderFieldsValidationModel::deleteAll(['service_provider_field_id' => $field->id]);
            }
        }

        try {
            if (!empty($providerModelFields[0]->attributes['id'])) {
                ServiceProviderFieldsModel::deleteAll(['service_provider_id' => $providerModel->id]);
            }

            if (!empty($providerModel->attributes['id'])) {
                $providerModel->delete();
            }

            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/service-provider']);
        } catch (\Exception $ex) {
            $this->renderAjax('serviceProvider/_form', ['model' => $providerModel]);
        }

        return $this->renderAjax('serviceProvider/_form', ['model' => $providerModel]);
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
        $service = ServiceModel::findOne(['id' => $id]);

        $providerModel = new ServiceProviderModel();
        $providerModel = $providerModel->findOne(['service_id' => $id]);

        $feesModel = new FeesModel();
        $feesModel = $feesModel->findAll(['service_id' => $id]);

        $providerModelFields = new ServiceProviderFieldsModel();
        $providerModelFields = $providerModelFields->findAll(['service_provider_id' => $providerModel->id ?? null]);

        foreach ($providerModelFields as $field) {
            $validation = new ServiceProviderFieldsValidationModel();
            $validation = $validation->findAll(['service_provider_field_id' => $field->id ?? null]);

            if (!empty($validation[0]->attributes['id'])) {
                ServiceProviderFieldsValidationModel::deleteAll(['service_provider_field_id' => $field->id]);
            }
        }

        try {
            if (!empty($providerModelFields[0]->attributes['id'])) {
                ServiceProviderFieldsModel::deleteAll(['service_provider_id' => $providerModel->id]);
            }

            if (!empty($feesModel[0]->attributes['id'])) {
                FeesModel::deleteAll(['service_id' => $id]);
            }

            if (!empty($providerModel->attributes['id'])) {
                $providerModel->delete();
            }

            if ($service) {
                $response = $service->delete();
            }

            \Yii::$app->session->setFlash('success', \Yii::t('manual/cabinet', 'success'));

            return $this->redirect(['moderator/index']);
        } catch (\Exception $exception) {
            \Yii::$app->session->setFlash('error', \Yii::t('manual/cabinet', $exception->getMessage()));

            return $this->redirect(['moderator/index']);
        }
    }

    public function actionFields($id)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ServiceProviderFieldsModel::find()
                ->select('service_provider_fields.*')
                ->from('kooma.service')
                ->innerJoin('kooma.service_provider', 'kooma.service_provider.service_id = kooma.service.id')
                ->innerJoin('kooma.service_provider_fields', 'kooma.service_provider_fields.service_provider_id = kooma.service_provider.id')
                ->where(['kooma.service_provider.id' => $id])
                ->orderBy('id'),
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
