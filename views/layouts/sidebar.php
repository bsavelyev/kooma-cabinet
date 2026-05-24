<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index" class="brand-link">
        <span class="font-weight-light">Kooma</span>
    </a>



    <!-- Sidebar -->
    <div class="sidebar">

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php

            use yii\bootstrap5\Nav;
            use yii\helpers\ArrayHelper;
            use yii\widgets\Menu;

            if ($role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId())) {
                ArrayHelper::toArray($role);

                if (ArrayHelper::keyExists('administrator', $role)) {
                    echo Nav::widget([
                        'items' => [
                            ['label' => Yii::t('menu', 'user'), 'iconStyle' => 'far', 'url' => ['admin/index']],
                            ['label' => Yii::t('menu', 'rbac'), 'iconStyle' => 'far', 'url' => ['admin/rbac']],
                        ],
                    ]);
                }
                if (ArrayHelper::keyExists('moderator', $role)) {
                    echo Nav::widget([
                        'items' => [
                            ['label' => Yii::t('menu', 'services'), 'iconStyle' => 'far', 'url' => ['moderator/index']],
                            ['label' => Yii::t('menu', 'services_provider'), 'iconStyle' => 'far', 'url' => ['moderator/service-provider']],
                            ['label' => Yii::t('menu', 'fees'), 'iconStyle' => 'far', 'url' => ['moderator-fees/index']],
                            ['label' => Yii::t('menu', 'limits'), 'iconStyle' => 'far', 'url' => ['moderator-limits/index']],
                            ['label' => Yii::t('menu', 'limits_by_merchant'), 'iconStyle' => 'far', 'url' => ['moderator-limits-by-merchant/index']],
                        ],
                    ]);
                }

                if (ArrayHelper::keyExists('financier', $role)) {
                    echo Nav::widget([
                        'items' => [
                            ['label' => Yii::t('menu', 'operations'), 'iconStyle' => 'far', 'url' => ['financier/operation?sort=-id']],
                            ['label' => Yii::t('menu', 'agents'), 'iconStyle' => 'far', 'url' => ['financier/agents']],
                            ['label' => Yii::t('menu', 'history'), 'iconStyle' => 'far', 'url' => ['financier/history?sort=-id']],
                        ],
                    ]);
                }
            }
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>