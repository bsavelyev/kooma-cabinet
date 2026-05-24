<?php

use yii\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use app\widgets\Alert;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'rbac');
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>
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
            <?php echo Html::button(Yii::t('cabinet', 'create'), ['data-value' => Url::to("create-rbac"), 'class' => 'btn btn-primary', 'id' => 'createModal']);?>
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
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'description',
                ],
                [
                    'attribute' => 'type',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{permissions}',
                    'header' => Yii::t('cabinet', 'actions'),
                    'buttons' => [
                        'update' => function ($model, $data) {
                            return Html::button('', ['data-value' => Url::to("update-rbac?name=$data->name"), 'class' => 'fa fa-fw fa-edit btn btn-link modalButton', ]);
                        },
                        'permissions' => function ($model, $data) {
                            return Html::button('', ['data-value' => Url::to("set-permissions?name=$data->name"), 'class' => 'fa fa-fw fa-user btn btn-link modalButton', ]);
                        }
                    ],
                ],
            ]
        ]);
?>
    </div>

</div>