<?php

use yii\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use app\widgets\Alert;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'limits');
$this->params['breadcrumbs'] = [['label' => $this->title]];
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
            <?php echo Html::button(Yii::t('cabinet', 'create'), ['data-value' => Url::to("moderator-limits/create"), 'class' => 'btn btn-primary', 'id' => 'createModal']); ?>
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
                    'attribute' => 'id',
                ],
                [
                    'attribute' => 'limitPeriod.name',
                ],
                [
                    'attribute' => 'value',
                ],
                [
                    'attribute' => 'limitValueType.name',
                ],
                [
                    'attribute' => 'subject.username',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{delete}',
                    'header' => Yii::t('cabinet', 'actions'),
                    'buttons' => [
                        'update' => function ($model, $data) {
                            return Html::button('', ['data-value' => Url::to("moderator-limits/update?id=$data->id"), 'class' => 'fa fa-fw fa-edit btn btn-link modalButton', 'title' => 'редактировать']);
                        },
                        'delete' => function ($model, $data) {
                            return Html::a('<span class="fas fa-trash btn btn-link button"></span>', "moderator-limits/delete?id=$data->id", [
                                'title' => 'удалить',
                            ]);
                        }
                    ],
                ],
            ]
        ]);
?>
    </div>
</div>