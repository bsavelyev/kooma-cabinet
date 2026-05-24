<?php

use app\enum\ServiceEnum;
use yii\bootstrap4\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\Pjax;

Pjax::begin();
$form = ActiveForm::begin([
    'id' => 'update-form',
    'enableAjaxValidation' => false,
    'options' => ['data' => ['pjax' => true]],
]);

echo $form->field($model, 'system_name')->textInput(['autofocus' => true]);
echo $form->field($model, 'user_name');
echo $form->field($model, 'icon');
echo $form->field($model, 'status')->dropDownList(ServiceEnum::values());
?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
