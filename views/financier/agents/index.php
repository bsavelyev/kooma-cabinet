<?php

use yii\grid\GridView;
use app\enum\AccountTypeEnum;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use app\widgets\Alert;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'agents');
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
    <div class="card-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}{summary}{pager}',
            'showOnEmpty' => false,
            'options' => ['class' => 'table-responsive'],
            'columns' => [
                [
                    'attribute' => 'subject.username',
                ],
                [
                    'attribute' => 'amount',
                ],
                [
                    'attribute' => 'status',
                ],
                [
                    'attribute' => 'type',
                    'value' => function ($model) {
                        return AccountTypeEnum::values()[$model['type']];
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{emission}{redemption}',
                    'header' => Yii::t('cabinet', 'actions'),
                    'buttons' => [
                        'emission' => function ($model, $data) {
                            return Html::button('', ['data-value' => Url::to("emission?id=$data->subject_id&type=$data->type"), 'class' => 'fas fa-arrow-down  modalButton', 'style' => 'color: #13dd35;', 'title' => 'Эмиссия']);
                        },
                        'redemption' => function ($model, $data) {
                            return Html::button('', ['data-value' => Url::to("redemption?id=$data->subject_id&type=$data->type"), 'class' => 'fas fa-arrow-up  modalButton', 'style' => 'color: #ed1a02;', 'title' => 'Гашение']);
                        }
                    ],
                ],
            ]
        ]);
?>
    </div>
</div>
