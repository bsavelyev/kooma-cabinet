<?php
use app\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

$this->title = 'Обновление данных';
$this->params['breadcrumbs'][] = $this->title;

Pjax::begin();
?>

    <div class="upload-form">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php $form = ActiveForm::begin([
            'id' => 'update-form',
            'enableAjaxValidation' => false,
            'options' => [
                'data' => ['pjax' => true],
                'enctype' => 'multipart/form-data',
            ],
            'fieldConfig' => [
                'options' => ['class' => 'form-group'],
            ],
        ]); ?>

        <?= $form->field($model, 'user_id')->dropDownList(
            ArrayHelper::map(User::find()->asArray()->all(), 'id', 'username'),
            ['prompt' => 'Выберите пользователя']
        )->label('Пользователь') ?>

        <?= $form->field($model, 'file')->fileInput(['accept' => '.xlsx'])->label('Загрузите Excel-файл (.xlsx)') ?>

        <div class="form-group">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?= Yii::t('cabinet', 'close') ?></button>
            <?= Html::submitButton(Yii::t('cabinet', 'copy_from_qiwi'), ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

<?php Pjax::end(); ?>