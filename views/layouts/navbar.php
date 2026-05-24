<?php

use yii\helpers\Html;

?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    <?php echo \yii\bootstrap5\Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'], // стили ul
        'items' => [
            Yii::$app->user->isGuest ? // Если пользователь гость, показыаем ссылку "Вход", если он авторизовался "Выход"
                ['label' => 'Вход', 'url' => ['//site/login']] :
                [
                    'label' => 'Выход (' . Yii::$app->user->getIdentity()->username . ')',
                    'url' => ['//site/logout'],
                ],
        ],
    ]);
?>

</nav>