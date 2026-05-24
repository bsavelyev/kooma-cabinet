<?php

use app\models\LimitPeriodModel;
use app\models\LimitValueTypeModel;
use app\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;

Pjax::begin();
$form = ActiveForm::begin([
    'id' => 'update-form',
    'enableAjaxValidation' => false,
    'options' => ['data' => ['pjax' => true]],
]);

echo $form->field($model, 'period')
    ->dropDownList(ArrayHelper::map(LimitPeriodModel::find()->asArray()->all(), 'code', 'name'));

echo $form->field($model, 'value');

echo $form->field($model, 'value_type')
    ->dropDownList(ArrayHelper::map(LimitValueTypeModel::find()->asArray()->all(), 'code', 'name'));

echo $form->field($model, 'merchant_id')
    ->dropDownList(ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username'));
?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
