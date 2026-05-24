<?php

use app\models\AuthItemModel;
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
echo $form->field($model, 'item_name')->dropDownList(ArrayHelper::map(AuthItemModel::find()->where(['type' => 1])->asArray()->all(), 'name', 'name'));
$userId = $model->user_id ? $model->user_id : $id;
echo $form->field($model, 'user_id')->textInput(['value' => User::find()->where(['id' => $userId])->one()->username]);?>

<div class="form-group">
    <button type="button" class="btn btn-default"
            data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
    <?= Html::submitButton(Yii::t('cabinet', 'save'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
</div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>
