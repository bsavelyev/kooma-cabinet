<?php

use app\enum\FeesStatusEnum;
use app\enum\FeesTypeEnum;
use app\enum\FeesValueTypeEnum;
use app\widgets\Alert;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'fees');
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<?php echo Alert::widget(); ?>

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
            <?php echo Html::button(Yii::t('cabinet', 'create'), ['data-value' => Url::to(['moderator-fees/create']), 'class' => 'btn btn-primary', 'id' => 'createModal']); ?>
        </div>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['moderator-fees/index']]); ?>
        <div class="row">
            <div class="col-md-3">
                <?php echo $form->field($model, 'service_system_name'); ?>
            </div>
            <div class="col-md-3">
                <?php echo $form->field($model, 'provider_username'); ?>
            </div>
            <div class="col-md-3">
                <?php echo $form->field($model, 'agent_username'); ?>
            </div>
            <div class="col-md-3">
                <?php echo $form->field($model, 'value'); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?php echo $form->field($model, 'min_value'); ?>
            </div>
            <div class="col-md-3">
                <?php echo $form->field($model, 'max_value'); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo Html::submitButton('Поиск', ['class' => 'btn btn-primary']); ?>
            <?php echo Html::resetButton('Сбросить', ['class' => 'btn btn-secondary']); ?>
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
                    'attribute' => 'provider_username',
                    'label' => 'Имя провайдера',
                    'value' => function ($model) {
                        return $model->provider ? $model->provider->username : '';
                    },
                ],
                [
                    'attribute' => 'agent_username',
                    'label' => 'Имя агента',
                    'value' => function ($model) {
                        return $model->agent ? $model->agent->username : '';
                    },
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        return FeesStatusEnum::values()[$model->status];
                    },
                ],
                [
                    'attribute' => 'value',
                ],
                [
                    'attribute' => 'type_id',
                    'value' => function ($model) {
                        return FeesTypeEnum::values()[$model->type_id];
                    },
                ],
                [
                    'attribute' => 'value_type',
                    'value' => function ($model) {
                        return FeesValueTypeEnum::values()[$model->value_type];
                    },
                ],
                [
                    'attribute' => 'start_date',
                ],
                [
                    'attribute' => 'end_date',
                ],
                [
                    'attribute' => 'create_date',
                ],
                [
                    'attribute' => 'status_change_date',
                ],
                [
                    'attribute' => 'min_value',
                ],
                [
                    'attribute' => 'max_value',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'header' => Yii::t('cabinet', 'actions'),
                    'buttons' => [
                        'update' => function ($url, $model) {
                            return Html::button('', ['data-value' => Url::to(['moderator-fees/update', 'id' => $model->id]), 'class' => 'fa fa-fw fa-edit btn btn-link modalButton']);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>