<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use kartik\detail\DetailView;
use kartik\datecontrol\DateControl;

/**
 * @var yii\web\View $this
 * @var <?= ltrim($generator->modelClass, '\\') ?> $model
 */

$this->title = $model-><?= $generator->getNameAttribute() ?>;
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">
    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'condensed' => false,
        'hover' => true,
        'mode' => Yii::$app->request->get('edit') == 't' ? DetailView::MODE_EDIT : DetailView::MODE_VIEW,
        'panel' => [
            'heading' => $this->title,
            'type' => DetailView::TYPE_INFO,
        ],
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {

        $format = $generator->generateColumnFormat($column);
        if($column->type == 'smallint' || $column->phpType === 'boolean'){
            echo
"            [
                'attribute' => '$column->name',
                'type' => DetailView::INPUT_DROPDOWN_LIST,
                'items' => ['key1'=>1,'key2'=>2],
            ],\n";
        }elseif($column->type === 'integer' && preg_match("/(date)$/i",$column->name)){
            echo
"            [
                'attribute' => '$column->name',
                'format' => [
                    'date', (isset(Yii::\$app->modules['datecontrol']['displaySettings']['date']))
                        ? Yii::\$app->modules['datecontrol']['displaySettings']['date']
                        : 'php:Y-m-d'
                ],
                'type' => DetailView::INPUT_WIDGET,
                'widgetOptions' => [
                    'class' => DateControl::className(),
                    'type' => DateControl::FORMAT_DATE
                ]
            ],\n";
        }elseif ($column->type === 'integer' && preg_match("/(datetime)$/i",$column->name)){
            echo
"            [
                'attribute' => '$column->name',
                'format' => [
                    'date', (isset(Yii::\$app->modules['datecontrol']['displaySettings']['datetime']))
                        ? Yii::\$app->modules['datecontrol']['displaySettings']['datetime']
                        : 'php:Y-m-d H:i:s'
                ],
                'type' => DetailView::INPUT_WIDGET,
                'widgetOptions' => [
                    'class' => DateControl::className(),
                    'type' => DateControl::FORMAT_DATE
                ]
            ],\n";
        }elseif ($column->type === 'char' && preg_match("/(time)$/i",$column->name)){
            echo
"            [
                'attribute' => '$column->name',
                'format' => [
                    'date', (isset(Yii::\$app->modules['datecontrol']['displaySettings']['time']))
                        ? Yii::\$app->modules['datecontrol']['displaySettings']['time']
                        : 'php:H:i:s'
                ],
                'type' => DetailView::INPUT_WIDGET,
                'widgetOptions' => [
                    'class' => DateControl::className(),
                    'type' => DateControl::FORMAT_DATE
                ]
            ],\n";
        }elseif(preg_match("/(image|images|img|picture|pic|thumb|thumbnail|cover|banner)$/i",$column->name)){
            echo
"            [
                    'attribute'=>'$column->name',
                    'format'=>['image',['width'=>'200px']],
                    'value'=>(\$model->{$column->name})?Yii::getAlias('@frontendLocalhost').'/'.\$model->{$column->name}.'?v='.\$model->updated_at:'upload_files/noPic.gif',
                    'type'=>DetailView::INPUT_FILE
            ],\n";
        }elseif(preg_match("/^(password|pass|passwd|passcode)$/i",$column->name)){
            echo
"            [
                    'attribute'=>'$column->name',
                    'type'=>DetailView::INPUT_PASSWORD
            ],\n";
        }elseif($column->type === 'text'){
            if(preg_match("/(content)/i",$column->name)){
                echo
"            [
                    // must mysql config content field is not null
                    'attribute'=>'$column->name',
                    'format' => 'raw',
                    'type' => DetailView::INPUT_WIDGET,
                    'widgetOptions' =>[
                        'class'=>'\\yii\\redactor\\widgets\\Redactor',
                        'clientOptions' => [
                            'imageManagerJson' => ['/redactor/upload/image-json'],
                            'imageUpload' => ['/redactor/upload/image'],
                            'fileUpload' => ['/redactor/upload/file'],
                            'lang' => 'zh_cn',
                            'plugins' => ['clips', 'fontcolor','imagemanager']
                        ]
                    ]
            ],\n";
            }else{
                echo
"               [
                    'attribute'=>'$column->name',
                    'type'=>DetailView::INPUT_TEXTAREA,
                    'options'=>[
                        'rows'=>6
                    ]
            ],\n";
            }
        }else {
            $columnDisplay = "            [\n";
            $columnDisplay .= "               'attribute' => '$column->name',\n";
            if($format !== ''){
                $columnDisplay .= "               'format'=>'$format',\n";
            }
            if($column->isPrimaryKey ||$column->name =='created_at' || $column->name == 'updated_at' ){
                $columnDisplay .= "               'displayOnly'=>true,\n";
            }
            $columnDisplay .= "            ],\n";
            echo $columnDisplay;
        }


    }
}
?>
        ],
        'deleteOptions' => [
            'url' => ['delete', 'id' => $model-><?=$generator->getTableSchema()->primaryKey[0]?>],
        ],
        'enableEditMode' => true,
    ]) ?>

</div>
