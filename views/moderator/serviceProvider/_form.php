<?php

use app\enum\ServiceEnum;
use yii\helpers\ArrayHelper;
use app\models\ServiceModel;
use app\models\User;
use yii\bootstrap4\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\Pjax;

Pjax::begin();
$form = ActiveForm::begin([
    'id' => 'update-form',
    'enableAjaxValidation' => false,
    'options' => ['data' => ['pjax' => true]],
]);

echo $form->field($model, 'service_id')
    ->dropDownList(ArrayHelper::map(ServiceModel::find()->asArray()->all(), 'id', 'system_name'));

echo $form->field($model, 'provider_id')
    ->dropDownList(ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username'));

echo $form->field($model, 'descr');
echo $form->field($model, 'external_service_id');
echo $form->field($model, 'status')->dropDownList(ServiceEnum::values());
?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
