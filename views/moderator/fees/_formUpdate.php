<?php

use app\enum\FeesStatusEnum;
use app\enum\FeesTypeEnum;
use app\enum\FeesValueTypeEnum;
use app\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use bs\Flatpickr\FlatpickrWidget;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

Pjax::begin();
$form = ActiveForm::begin([
    'id' => 'update-form',
    'enableAjaxValidation' => false,
    'options' => ['data' => ['pjax' => true]],
]);

echo $form->field($model, 'status')->dropDownList(FeesStatusEnum::values());

echo $form->field($model, 'type_id')->dropDownList(FeesTypeEnum::values());

echo $form->field($model, 'value');

echo $form->field($model, 'min_value');

echo $form->field($model, 'max_value');

echo $form->field($model, 'provider_id')->dropDownList(ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username'));

echo $form->field($model, 'agent_id')->dropDownList(ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username'));

echo $form->field($model, 'value_type')->dropDownList(FeesValueTypeEnum::values());
?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
