<?php

use yii\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use app\widgets\Alert;
use yii\helpers\Url;

$this->title = Yii::t('menu', 'welcome');
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>
<?= Alert::widget() ?>

