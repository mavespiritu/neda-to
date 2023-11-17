<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@file' => dirname(__DIR__),
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'view' => [
         'theme' => [
             'pathMap' => [
                '@frontend/views' => '@common/modules/procurement/views/layouts'
             ],
         ],
        ],
    ],
    'modules' => [
        'procurement' => [
            'class' => 'common\modules\procurement\Procurement',
        ],
        'v1' => [
            'class' => 'common\modules\v1\V1',
        ],
        'file' => [
            'class' => 'file\FileModule',
            'webDir' => 'files',
            'tempPath' => '@frontend/web/temp',
            'storePath' => '@frontend/web/store',
            'rules' => [ // Правила для FileValidator
                'maxFiles' => 20,
                'maxSize' => 1024 * 1024 * 20 // 20 MB
            ],
        ],
        'user' => [
            'class' => 'markavespiritu\user\Module',
            'admins' => ['markavespiritu'],
            'enableRegistration' => true,
            'enableConfirmation' => false,
            'enablePasswordRecovery' => false,
            'controllerMap' => [
                'admin' => [
                    'class' => 'markavespiritu\user\controllers\AdminController',
                    'as access' => [
                        'class' => 'yii\filters\AccessControl',
                        'rules' => [
                            [
                                'allow' => true,
                                'roles' => ['Administrator'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'rbac' => [
            'class' => 'markavespiritu\rbac\RbacWebModule',
        ],
        'sso' => [
            'class' => 'common\modules\sso\Sso',
        ]
    ]
];
