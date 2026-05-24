<?php

use app\models\FieldsTypeModel;
use app\models\ValidationTypeModel;
use yii\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use app\widgets\Alert;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'services-fields-validation');
//$this->params['breadcrumbs'] = [['label' => $this->title]];
?>
<?= Alert::widget() ?>

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
            <?php echo Html::button(Yii::t('cabinet', 'create'), ['data-value' => Url::to("create-service-fields-validation?id=$id"), 'class' => 'btn btn-primary', 'id' => 'createModal']);?>
        </div>
    </div>
    <div class="card-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}{summary}{pager}',
            'showOnEmpty' => false,
            'options' => ['class' => 'table-responsive'],
            'columns' => [
                [
                    'attribute' => 'service_provider_field_id'
                ],
                [
                    'attribute' => 'param'
                ],
                [
                    'attribute' => 'type',
                    'value' => function ($model) {
                        return ValidationTypeModel::findOne(['code' => $model['type']])['name'];
                    },
                ],
                [
                    'attribute' => 'descr'
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}',
                    'header' => Yii::t('cabinet', 'actions'),
                    'buttons' => [
                        'update' => function ($model, $data) {
                            return Html::button('', ['data-value' => Url::to("update-service-fields-validation?id={$_GET['id']}"), 'class' => 'fa fa-fw fa-edit btn btn-link modalButton', ]);
                        }
                    ],
                ],
            ]
        ]);
?>
    </div>
</div>

<script>
    $(function () {
        $('.modalButton').click(function (event) {
            event.preventDefault()
            $('#update').modal('show').find('#modalContentUpdate').load($(this).attr('data-value'));
        })
        $('#createModal').click(function (event) {
            event.preventDefault()
            $('#create').modal('show').find('#modalContentCreate').load($(this).attr('data-value'));
        })
    })
</script>
