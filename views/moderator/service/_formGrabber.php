<?php

use app\models\User;
use yii\bootstrap4\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

Pjax::begin();
$form = ActiveForm::begin([
    'id' => 'update-form',
    'enableAjaxValidation' => false,
    'options' => ['data' => ['pjax' => true]],
]);

echo $form->field($model, 'services_ids')->textInput(['autofocus' => true]);
echo $form->field($model, 'user_id')->dropDownList(ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username'));
?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'copy_from_wooppay'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
