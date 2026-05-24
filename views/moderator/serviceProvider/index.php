<?php

use app\enum\ServiceEnum;
use yii\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use app\widgets\Alert;
use yii\helpers\Url;
use yii\bootstrap4\ActiveForm;

$this->title = Yii::t('menu', 'services');
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<?= Alert::widget() ?>

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
            <?php echo Html::button(Yii::t('cabinet', 'create'), ['data-value' => Url::to("create-service-provider"), 'class' => 'btn btn-primary', 'id' => 'createModal']); ?>
        </div>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['service-provider']]); ?>
        <div class="row">
            <div class="col-md-3">
                <?= $form->field($model, 'service_system_name')?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'subject_username')->textInput()?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'external_service_id')?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'descr')?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('Сбросить', ['class' => 'btn btn-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>

        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}{summary}{pager}',
            'showOnEmpty' => false,
            'options' => ['class' => 'table-responsive'],
            'columns' => [
                [
                    'attribute' => 'service_system_name',
                    'label' => 'Название сервиса',
                    'value' => function ($model) {
                        return $model->service ? $model->service->system_name : '';
                    },
                ],
                [
                    'attribute' => 'subject_username',
                    'value' => function ($model) {
                        return $model->subject ? $model->subject->username : '';
                    },
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        return ServiceEnum::values()[$model->status];
                    },
                ],
                [
                    'attribute' => 'descr',
                ],
                [
                    'attribute' => 'external_service_id',
                ],
                [
                    'attribute' => 'create_date',
                ],
                [
                    'attribute' => 'status_change_date',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{delete}',
                    'header' => Yii::t('cabinet', 'actions'),
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::button('', ['data-value' => Url::to(['update-service-provider', 'id' => $model->id]), 'class' => 'fa fa-fw fa-edit btn btn-link modalButton']);
                        },
                        'delete' => function ($url, $model) {
                            return Html::a('<span class="fas fa-trash btn btn-link button"></span>', ['delete-service-provider', 'id' => $model->id], [
                                'title' => 'удалить',
                            ]);
                        },
                    ],
                ],
            ]
        ]);
        ?>
    </div>
</div>
?>