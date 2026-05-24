<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'replenishmentService' => 'replenishment',
    'dischargeService' => 'discharge',
    'username' => '77751234567',
    'wooppayUrl' => YII_ENV_PROD ? 'https://api-core.wooppay.com/v1/' : 'https://api-core.dev.wooppay.com/v1/',
    'operation' => [
        'prodHost' => 'cabinet.koomapay.kz',
        'ulServiceIds' => [
            'prod' => [53, 52, 51],
            'test' => [2797, 2796, 2798],
        ],
        'flServiceIds' => [
            'prod' => [57, 56, 55, 54],
            'test' => [2792, 2793, 2797, 2795],
        ],
    ],
    'payment_service' => [
        'qiwi' => [
            'baseUrl' => 'https://xml1.qiwi.com/xmlgate/xml.jsp',
            'login' => 'test login',
            'signAlg' => 'MD5',
            'sign' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            'terminal' => '122345678',
            'log' => true,
            'koomaID' => YII_ENV_PROD ? 1 : 2,
        ],
    ],
];
