<?php

use app\models\ServiceProviderFieldsModel;
use app\models\ValidationTypeModel;
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

echo $form->field($model, 'service_provider_field_id')->textInput(['value' => $id]);

echo $form->field($model, 'param');

echo $form->field($model, 'type')->dropDownList(ArrayHelper::map(ValidationTypeModel::find()->asArray()->all(), 'code', 'name'));

echo $form->field($model, 'descr');?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
