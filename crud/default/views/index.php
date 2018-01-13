<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;


/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

function generateColumnHidden($column){
    if($column->type === 'text'){
        return true;
    }elseif($column->type === 'string' && preg_match("/(image|images|img|picture|pic|thumb|thumbnail|cover|banner)$/i",$column->name)){
        return true;
    }else{
        return false;
    }
}
function generateColumnFilter($column){
    if ($column->phpType === 'boolean') {
        return true;
    } elseif ($column->type === 'smallint') {
        return true;
    }else{
        return false;
    }
}
/**
 * index page column if hidden
 * @param $column
 * @return bool
 */

echo "<?php\n";


?>

use yii\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "kartik\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
use yii\widgets\Pjax;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
<?= !empty($generator->searchModelClass) ? " * @var " . ltrim($generator->searchModelClass, '\\') . " \$searchModel\n" : '' ?>
 */

$this->title = <?= $generator->generateString(Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass)))) ?>;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
<?php if (!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?php /* echo " ?>Html::a(<?= $generator->generateString('Create {modelClass}', ['modelClass' => Inflector::camel2words(StringHelper::basename($generator->modelClass))]) ?>, ['create'], ['class' => 'btn btn-success'])<?= "*/ " ?> ?>
    </p>

<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?php Pjax::begin(); echo " ?>GridView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            ['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "            [
               'attribute' => '$name'
            ],"."\n";
        } else {
            echo "            /*[
               'attribute' => '$name'
            ],*/"."\n";
        }
    }
} else {

    foreach ($tableSchema->columns as $column) {

        $format = $generator->generateColumnFormat($column);
        $hidden = generateColumnHidden($column);
        $filter = generateColumnFilter($column);
        $if_hidden_str = $hidden?"//":'';
        $columnDisplay = $if_hidden_str."            [\n";
        $columnDisplay .= $if_hidden_str."               'attribute' => '$column->name',\n";
        if($format !== ''){
            $columnDisplay .= $if_hidden_str."               'format'=>'$format',\n";
        }
        if($filter == true){
            $columnDisplay .= $if_hidden_str."               'filter'=>['key1'=>'value1','key2'=>'value2'],\n";
        }
        if($column->isPrimaryKey || $column->autoIncrement ){
            $columnDisplay .= $if_hidden_str."               'contentOptions'=>['width'=>90],\n";
        }
        $columnDisplay .= $if_hidden_str."            ],\n";
        echo $columnDisplay ."\n";

    }
}
?>

            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'update' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                            Yii::$app->urlManager->createUrl(['<?= (empty($generator->moduleID) ? '' : $generator->moduleID . '/') . $generator->controllerID?>/view', <?= $urlParams ?>, 'edit' => 't']),
                            ['title' => Yii::t('yii', 'Edit'),]
                        );
                    }
                ],
            ],
        ],
        'responsive' => true,
        'hover' => true,
        'condensed' => true,
        'floatHeader' => true,

        'panel' => [
            'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-th-list"></i> '.Html::encode($this->title).' </h3>',
            'type' => 'info',
            'before' => Html::a("<i class='glyphicon glyphicon-plus'></i> <?= Yii::t('app','add');?>", ['create'], ['class' => 'btn btn-success']),
            'after' => Html::a("<i class='glyphicon glyphicon-repeat'></i> <?= Yii::t('app','resetList');?>", ['index'], ['class' => 'btn btn-info']),
            'showFooter' => false
        ],
    ]); Pjax::end(); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
<?php endif; ?>

</div>
