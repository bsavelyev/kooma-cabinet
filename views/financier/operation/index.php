<?php

use app\enum\AccountTypeEnum;
use app\enum\OperationStatusEnum;
use app\models\OperationModel;
use app\models\ServiceModel;
use app\models\User;
use bs\Flatpickr\FlatpickrWidget;
use yii\grid\GridView;
use yii\bootstrap4\Modal;
use app\widgets\Alert;
use yii\helpers\ArrayHelper;
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
    'title' => Yii::t('cabinet', 'change_status_of_several_operations'),
    'options' => [
        'id' => 'create',
    ],
]);

echo "<div id='modalContentCreate'></div>";
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
        Url::to('/financier/operation?sort=-id')
    ],
    'options' => ['class' => 'filter-form'],
]);
$users = ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username');
$services = ArrayHelper::map(ServiceModel::find()->asArray()->all(), 'id', 'system_name');
$users[0] = '';
ksort($users);
$services[0] = '';
$services['UL'] = 'UL';
$services['FL'] = 'FL';
ksort($services);

echo Html::hiddenInput('page', Yii::$app->request->post('page', 1));
echo Html::hiddenInput('format', Yii::$app->request->post('format', 0));

?>
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-sm" style="float: left; margin-right: 50px; margin-top: 20px">
                <?= $form->field($historyForm, 'id')->textInput(['autofocus' => true]);

echo $form->field($historyForm, 'sender_id')->dropDownList($users);

echo $form->field($historyForm, 'receiver_id')->dropDownList($users);

echo $form->field($historyForm, 'status')->dropDownList(OperationStatusEnum::values());
?>
            </div>
            <div class="col-sm" style="float: left; margin-right: 50px; margin-top: 20px">
                <?= $form->field($historyForm, 'from_create_date')->widget(FlatpickrWidget::class, [
    'options' => ['class' => 'form-control',
        'title' => 'Выберите период'],
    'clientOptions' => [
        'enableTime' => true,
        'dateFormat' => 'Y-m-d H:i:S',
        'time_24hr' => true,
    ]])->label('Период создания с');

echo $form->field($historyForm, 'to_create_date')->widget(FlatpickrWidget::class, [
    'options' => ['class' => 'form-control'],
    'clientOptions' => [
        'enableTime' => true,
        'dateFormat' => 'Y-m-d H:i:S',
        'time_24hr' => true,
    ]])->label('Период создания по');

echo $form->field($historyForm, 'from_status_change_date')->widget(FlatpickrWidget::class, [
    'options' => ['class' => 'form-control',
        'title' => 'Выберите период'],
    'clientOptions' => [
        'enableTime' => true,
        'dateFormat' => 'Y-m-d H:i:S',
        'time_24hr' => true,
    ]])->label('Период изменения с');

echo $form->field($historyForm, 'to_status_change_date')->widget(FlatpickrWidget::class, [
    'options' => ['class' => 'form-control'],
    'clientOptions' => [
        'enableTime' => true,
        'dateFormat' => 'Y-m-d H:i:S',
        'time_24hr' => true,
    ]])->label('Период изменения по');

echo $form->field($historyForm, 'service_id')
    ->listBox($services, ['multiple' => true]);

echo $form->field($historyForm, 'amount');
?>
            </div>
            <div class="col-sm" style="float: left; margin-right: 50px; margin-top: 20px">
                <?= $form->field($historyForm, 'billing_id');

echo $form->field($historyForm, 'payment_account');

echo $form->field($historyForm, 'type_id')->dropDownList(OperationModel::values());

echo $form->field($historyForm, 'ext_id');
?>

                <div class="form-group">
                    <?= Html::submitButton('Найти', ['class' => 'btn btn-primary',
        'name' => 'format',
        'value' => 0,]) ?>
                </div>

                <div class="form-group">
                    <?= Html::submitButton('exportCSV', [
        'class' => 'btn btn-primary',
        'id' => 'export',
        'style' => 'background-color:green;border-color:green;',
        'name' => 'format',
        'value' => 'csv',
        'onclick' => 'setFormAction("exportCSV")'
    ]) ?>
                </div>

                <div class="form-group">
                    <?= Html::submitButton('exportXLSX', [
        'class' => 'btn btn-primary',
        'id' => 'export',
        'style' => 'background-color:green;border-color:green;',
        'name' => 'format',
        'value' => 'xlsx',
        'onclick' => 'setFormAction("exportXLSX")'
    ]) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>

            <div class="card-header">
                <div class="card-title">
                    <?php echo Html::button(Yii::t('cabinet', 'change_status_of_several_operations'), ['data-value' => Url::to("/financier/change-status-of-several-operations"), 'class' => 'btn btn-primary', 'id' => 'createModal']);?>
                </div>
            </div>
        </div>

    </div>

    <script>
        function setFormAction(action) {
            var form = document.getElementById('history-form');
            if (action === 'exportCSV') {
                form.action = '<?= \yii\helpers\Url::to(['financier/export?format=csv']) ?>';
            } else if (action === 'exportXLSX') {
                form.action = '<?= \yii\helpers\Url::to(['financier/export?format=xlsx']) ?>';
            }
        }
    </script>


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
        'attribute' => 'sender_id',
        'value' => function ($model) {
            return User::findOne(['id' => [$model->sender_id]])->username;
        },
    ],
    [
        'attribute' => 'receiver_id',
        'value' => function ($model) {
            return User::findOne(['id' => [$model->receiver_id]])->username;
        },
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
        'attribute' => 'status_change_date',
                    ],
    [
        'attribute' => 'service_id',
        'value' => function ($model) {
            return ServiceModel::find()->where(['id' => $model['service_id']])->one()->system_name;
        },
                    ],
    [
        'attribute' => 'ext_id',
                    ],
    [
        'attribute' => 'payment_account',
                    ],
    [
        'attribute' => 'amount',
                    ],
    [
        'attribute' => 'billing_id',
                    ],
    [
        'attribute' => 'type_id',
        'value' => function ($model) {
            return OperationModel::values()[$model['type_id']];
        },
                    ],
    [
        'attribute' => 'sender_account_type',
        'value' => function ($model) {
            return AccountTypeEnum::values()[$model['sender_account_type']];
        },
                    ],
    [
        'attribute' => 'descr',
                    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{changeStatus}',
        'header' => Yii::t('cabinet', 'actions'),
        'buttons' => [
            'changeStatus' => function ($model, $data) {
                $url = Url::to(["change-status", 'id' => $data->id]); // Генерация URL
                return Html::a('', 'javascript:void(0);', [
                    'class' => 'fa fa-fw fa-edit changeStatus', // CSS-класс для кнопки
                    'title' => 'Сменить статус на новый',
                    'data-url' => $url, // Передаём URL в data-атрибут
                    'data-id' => $data->id // Передаём ID в data-атрибут (если нужно)
                ]);
            },
        ],
                    ]
]
            ]);
?>
        </div>
    </div>
</div>

<?php
$this->registerJs("
    $(document).on('click', '.changeStatus', function (e) {
        e.preventDefault(); // Отключаем стандартное поведение ссылки

        const url = $(this).data('url'); // Получаем URL из data-атрибута

        $.ajax({
            url: url,
            type: 'POST', // Используем POST-запрос
            data: {
                _csrf: yii.getCsrfToken(), // Добавляем CSRF-токен
            },
            success: function (response) {
                if (response.success) {
                    // Действия при успешном выполнении
                    alert('Статус успешно изменён!');
                } else {
                    alert('Ошибка: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Ошибка AJAX:', error);
                alert('Произошла ошибка, попробуйте снова.');
            }
        });
    });
");
?>