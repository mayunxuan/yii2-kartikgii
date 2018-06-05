# yii2-kartikgii
Gii Generator base on Kartik-V extension https://github.com/kartik-v

## how to use

1. use composer install
```
composer require mayunxuan/yii2-kartikgii
```

2. modify my_project_root/backend/config/main-local.php
```
if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
    //add code
    $config['modules']['gii']['generators'] = [
        'kartik-gii-crud' => ['class' => 'mayunxuan\kartikgii\crud\Generator'],
        'kartik-gii-model' => ['class' => 'mayunxuan\kartikgii\model\Generator']
    ];
}
```

3. modify my_project_root/common/config/bootstrap.php
```
//add code
Yii::setAlias('@backendHost', 'http://admin.my-backend.org');
Yii::setAlias('@frontendHost', 'http://my-frontend.org');
```
4. modify my_project_root/common/config/main.php, add code:
```
    'modules' => [
        'datecontrol' =>  [
            'class' => 'kartik\datecontrol\Module',

            // format settings for displaying each date attribute
            'displaySettings' => [
                'date' => 'php:Y-m-d',
                'time' => 'php:H:i:s',
                'datetime' => 'php:Y-m-d H:i:s',
            ],

            // format settings for saving each date attribute
            'saveSettings' => [
                'date' => 'php:U',
                'time' => 'php:H:i:s',
                'datetime' => 'php:U',
            ],
            // automatically use kartik\widgets for each of the above formats
            'autoWidget' => true,

        ],
        'redactor' => [
            'class' => 'yii\redactor\RedactorModule',
            'uploadDir' => '@frontend/web/redactor/uploadfolder',
            'uploadUrl' => Yii::getAlias("@frontendHost").'/redactor/uploadfolder',
            'imageAllowExtensions'=>['jpg','png','gif'],
        ],
    ],
```

