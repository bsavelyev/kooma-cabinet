<?php

use app\enum\SubjectEnum;
use yii\bootstrap4\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\Pjax;

Pjax::begin();
$form = ActiveForm::begin([
    'id' => 'update-form',
    'enableAjaxValidation' => false,
    'options' => ['data' => ['pjax' => true]],
]);

echo $form->field($model, 'username')->textInput(['autofocus' => true]);

echo $form->field($model, 'status')->dropDownList(SubjectEnum::values());

echo $form->field($model, 'password_hash'); ?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
