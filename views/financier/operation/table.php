<?php

echo \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}{summary}{pager}',
    'showOnEmpty' => false,
    'options' => ['class' => 'table-responsive'],
    'columns' => [
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'sender_login',
        ],
        [
            'attribute' => 'receiver_login',
        ],
        [
            'attribute' => 'status',
            'value' => function ($model) {
                return \app\enum\OperationStatusEnum::values()[$model['status']];
            },
        ],
        [
            'attribute' => 'create_date',
        ],
        [
            'attribute' => 'status_change_date',
        ],
        [
            'attribute' => 'service_name',
        ],
        [
            'attribute' => 'ext_id',
        ],
        [
            'attribute' => 'payment_account',
        ],
        [
            'attribute' => 'amount',
        ],
        [
            'attribute' => 'payment_account',
        ],
        [
            'attribute' => 'descr',
        ],
    ]
]);
