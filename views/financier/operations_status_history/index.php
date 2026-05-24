<?php

use app\enum\OperationStatusEnum;
use yii\grid\GridView;
use yii\bootstrap4\Modal;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;


$this->title = Yii::t('menu', 'operations');
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

<?php
$this->registerJs('
    $(".pagination-link").on("click", function(e) {
        e.preventDefault();
        var page = $(this).data("page") + 1; // Получить номер страницы из ссылки (нумерация начинается с 0)
        $("#history-form input[name=page]").val(page);
        $("#history-form input[name=format]").val(0);
        $("#history-form").submit();
    });
');
?>
<?php
$form = ActiveForm::begin([
    'id' => 'history-form',
    'method' => 'post',
    'action' => [
        Url::to('/financier/history?sort=-id')
    ],
    'options' => ['class' => 'filter-form'],
]);

echo Html::hiddenInput('page', Yii::$app->request->post('page', 1));
echo Html::hiddenInput('format', Yii::$app->request->post('format', 0));

?>
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-sm" style="float: left; margin-right: 50px; margin-top: 20px">
                <?= $form->field($historyForm, 'id')->textInput(['autofocus' => true]);

                echo $form->field($historyForm, 'operation_id');

                echo $form->field($historyForm, 'success')->dropDownList(['' => '' ,true => 'Успешная', false => 'Неуспешная']);

                echo $form->field($historyForm, 'status')->dropDownList(OperationStatusEnum::values());
                ?>
            </div>
            <div class="col-sm" style="float: left; margin-right: 50px; margin-top: 20px">

                <div class="form-group">
                    <?= Html::submitButton('Найти', ['class' => 'btn btn-primary',
                        'name' => 'format',
                        'value' => 0,]) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="card">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'pager' => [
                    'class' => LinkPager::class,
                    'options' => ['class' => 'pagination'],
                    'linkOptions' => [
                        'class' => 'pagination-link',
                    ],
                ],
                'layout' => '{items}{summary}{pager}',
                'showOnEmpty' => false,
                'options' => ['class' => 'table-responsive'],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'filterInputOptions' => ['maxlength' => 128],
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            return OperationStatusEnum::values()[$model['status']];
                        },
                    ],
                    [
                        'attribute' => 'create_date',
                    ],
                    [
                        'attribute' => 'operation_id',
                    ],
                    [
                        'attribute' => 'description',
                    ],
                    [
                        'attribute' => 'success',
                        'value' => function ($model) {
                            return $model['success'] === true ? 'Успешная' : 'Неуспешная';
                        },
                    ]
                ]
            ]);
            ?>
        </div>
    </div>
</div>
