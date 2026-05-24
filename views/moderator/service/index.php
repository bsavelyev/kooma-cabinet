<?php

use app\enum\ServiceEnum;
use app\models\ServiceProviderModel;
use app\widgets\Alert;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'services');
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<?php echo Alert::widget(); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<?php
Modal::begin([
    'title' => Yii::t('cabinet', 'create'),
    'options' => [
        'id' => 'create',
    ],
]);
echo "<div id='modalContentCreate'></div>";
Modal::end();
?>

<?php
Modal::begin([
    'title' => Yii::t('cabinet', 'update'),
    'options' => [
        'id' => 'update',
    ],
]);
echo "<div id='modalContentUpdate'></div>";
Modal::end();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <?php echo Html::button(Yii::t('cabinet', 'copy_from_wooppay'), ['data-value' => Url::to('moderator/service-grabber'), 'class' => 'btn btn-primary', 'id' => 'createModal']); ?>
            <?php echo Html::button(Yii::t('cabinet', 'copy_from_qiwi'), ['data-value' => Url::to('moderator/service-grabber-qiwi'), 'class' => 'btn btn-primary', 'id' => 'createModalQiwi']); ?>
            <?php echo Html::button(Yii::t('cabinet', 'create'), ['data-value' => Url::to('moderator/create-service'), 'class' => 'btn btn-primary', 'id' => 'createServiceModal']); ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Форма поиска -->
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
            'options' => ['class' => 'form-inline mb-3'],
        ]); ?>

        <div class="row">
            <div class="col-md-2">
                <?php echo $form->field($searchModel, 'id')->textInput(['placeholder' => Yii::t('cabinet', 'ID')])->label(false); ?>
            </div>
            <div class="col-md-3">
                <?php echo $form->field($searchModel, 'system_name')->textInput(['placeholder' => Yii::t('cabinet', 'System Name')])->label(false); ?>
            </div>
            <div class="col-md-3">
                <?php echo $form->field($searchModel, 'user_name')->textInput(['placeholder' => Yii::t('cabinet', 'User Name')])->label(false); ?>
            </div>
            <div class="col-md-2">
                <?php echo $form->field($searchModel, 'status')->dropDownList(
                    ['' => Yii::t('cabinet', 'All')] + ServiceEnum::values(),
                    ['prompt' => Yii::t('cabinet', 'Select Status')]
                )->label(false); ?>
            </div>
            <div class="col-md-2">
                <?php echo Html::submitButton(Yii::t('cabinet', 'Search'), ['class' => 'btn btn-primary']); ?>
                <?php echo Html::a(Yii::t('cabinet', 'Reset'), ['index'], ['class' => 'btn btn-secondary']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>

        <!-- Таблица -->
        <?php echo GridView::widget([
                    'dataProvider' => $dataProvider,
                    'layout' => '{items}{summary}{pager}',
                    'showOnEmpty' => false,
                    'options' => ['class' => 'table-responsive'],
                    'columns' => [
                        [
                            'attribute' => 'id',
                        ],
                        [
                            'attribute' => 'system_name',
                        ],
                        [
                            'attribute' => 'user_name',
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($model) {
                                return ServiceEnum::values()[$model['status']];
                            },
                        ],
                        [
                            'attribute' => 'create_date',
                ],
                        [
                            'attribute' => 'status_change_date',
                ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{fields}{delete}',
                            'header' => Yii::t('cabinet', 'actions'),
                            'buttons' => [
                                'update' => function ($model, $data) {
                                    return Html::button('', ['data-value' => Url::to("moderator/update-service?id={$data->id}"), 'class' => 'fa fa-fw fa-edit btn btn-link modalButton', 'title' => 'редактировать']);
                                },
                                'fields' => function ($model, $data) {
                                    $provider_id = '';
                                    if (isset(ServiceProviderModel::findOne(['service_id' => $data->id])->id)) {
                                        $provider_id = ServiceProviderModel::findOne(['service_id' => $data->id])->id;

                                        return Html::button('', ['class' => 'fas fa-align-justify btn btn-link', 'onclick' => 'window.location.href = "'.Yii::$app->urlManager->createUrl(['moderator/fields?id='.$provider_id]).'"', 'title' => 'список полей']);
                                    }

                                    return Html::label('   нет связи с провайдером');
                                },
                                'delete' => function ($model, $data) {
                                    return Html::button('', ['class' => 'fas fa-trash btn btn-link', 'onclick' => 'window.location.href = "'.Yii::$app->urlManager->createUrl(["moderator/delete-service?id={$data->id}"]).'"', 'title' => 'удалить']);
                                },
                            ],
                ],
                    ],
                ]); ?>
    </div>
</div>