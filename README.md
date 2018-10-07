# yii2-kartikgii
Gii 生成器是基于 https://github.com/kartik-v

可以根据字段的正则匹配,轻松使用kartik

|匹配到的字段|使用的控件|模型中使用的rule|
|--|--|--|
|image images img picture pic thumb thumbnail cover banner|kartik\widgets\FileInput|file|
|url link|默认|url|
|color|kartik\widgets\ColorInput|string|
|created_at updated_at|不会显示|date|
|content|\yii\redactor\widgets\Redactor|string|

如果是 small_img 也会被当成上传文件对待.

其他作用如下:

+ 默认每个表生成的模型自带场景.场景是被注释的,方便修改使用
+ 模型中默认生成beforeSave方法.用来自动添加创建时间和更新时间.后面更新可能会用TimestampBehavior取代
+ modelSearch 中生成一些代码处理时间搜索
+ 默认生成的控制器继承commonController类.这是为了方便用户登录验证以及权限认证.下面是demo
```
<?php

namespace backend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;


class CommonController extends Controller
{
    protected $actions = ['*'];
    protected $except = [];
    protected $mustLogin = [];
    protected $filter = [];
    protected $noRbac=["site/login","site/logout","site/error"];

    public function behaviors()
    {
        return [
            'access' => [
                'class' =>AccessControl::className(),
                'only' => $this->actions,
                'except' => $this->except,
                'rules' => [
                    [
                        'allow' => false,
                        'actions' => empty($this->mustlogin) ? [] : $this->mustlogin,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => empty($this->mustlogin) ? [] : $this->mustlogin,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => $this->filter,
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        //这里写权限控制代码
    }
}
```
控制器中也有相应模版,根据项目需要自行修改
```
public $mustlogin =["index","view","create","delete",'list'];
public $filter = ['delete' => ['post']];
```

如何使用?
1.安装

composer require mayunxuan/yii2-kartikgii

2.配置
common/main.php
```
<?php
return [
  //.....
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
        //.....
    ],
];
```
请务必按照上述方式配置datecontrol.日期格式为yyyy-mm-d
redactor是一个富文本编辑器,路径可以自行配置

3.加入gii模块
backend/main-local.php
```
<?php

//....
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
    //加入下面代码
    $config['modules']['gii']['generators'] = [
        'kartik-gii-crud' => ['class' => 'mayunxuan\kartikgii\crud\Generator'],
        'kartik-gii-model' => ['class' => 'mayunxuan\kartikgii\model\Generator']
    ];
}

return $config;
```

问题:
如果显示的日期格式不正确,可以在main.php的modules中配置formatter
```
'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'timeZone'=>'PRC',
            'defaultTimeZone'=>'PRC',
            'timeFormat'=>'HH:mm:ss',
            'datetimeFormat'=>'yyyy-MM-dd HH:mm:ss',
        ],
```

感谢 warrence/yii2-kartikgii https://packagist.org/packages/warrence/yii2-kartikgii
这个工具是在warrence/yii2-kartikgii的代码上修改的.只是想少些更多的重复代码.
感谢给我第一颗星的人.让我能有信心完善文档继续更新.

