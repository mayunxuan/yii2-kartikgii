<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mayunxuan\kartikgii\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\web\Controller;

class Generator extends \yii\gii\generators\crud\Generator
{
    public $modelClass;
    public $moduleID;
    public $controllerClass;
    public $baseControllerClass = 'yii\web\Controller';
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';

    public $uploadPregPart = 'image|images|img|picture|pic|thumb|thumbnail|cover|banner';
    public $urlPregPart = 'url|link';
    public $textEditorPregPart = 'content';
    public $hiddenFiledOptions = ['created_at','updated_at'];
    public $colorPregPart = 'color';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Kartikgii CURD Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates a controller and views that implement CRUD (Create, Read, Update, Delete)
            operations for the specified data model.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['moduleID', 'controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType'], 'required'],
            [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
            [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
            [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
            [['controllerClass', 'searchModelClass'], 'validateNewClass'],
            [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
            [['modelClass'], 'validateModelClass'],
            [['moduleID'], 'validateModuleID'],
            [['enableI18N'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'moduleID' => 'Module ID',
            'controllerClass' => 'Controller Class',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'searchModelClass' => 'Search Model Class',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class, .e.g, <code>app\controllers\PostController</code>.
                The controller class name should follow the CamelCase scheme with an uppercase first letter',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'moduleID' => 'This is the ID of the module that the generated controller will belong to.
                If not set, it means the controller will belong to the application.',
            'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
            'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['controller.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['baseControllerClass', 'moduleID', 'indexWidgetType']);
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }

    /**
     * Checks if model ID is valid
     */
    public function validateModuleID()
    {
        if (!empty($this->moduleID)) {
            $module = Yii::$app->getModule($this->moduleID);
            if ($module === null) {
                $this->addError('moduleID', "Module '{$this->moduleID}' does not exist.");
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');

        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
        ];

        if (!empty($this->searchModelClass)) {
            $searchModel = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\') . '.php'));
            $files[] = new CodeFile($searchModel, $this->render('search.php'));
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }

        return $files;
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID()
    {
        $pos = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, -10);

        return Inflector::camel2id($class);
    }

    /**
     * @return string the action view file path
     */
    public function getViewPath()
    {
        $module = empty($this->moduleID) ? Yii::$app : Yii::$app->getModule($this->moduleID);

        return $module->getViewPath() . '/' . $this->getControllerID() ;
    }

    public function getNameAttribute()
    {
        foreach ($this->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /** @var \yii\db\ActiveRecord $class */
        $class = $this->modelClass;
        $pk = $class::primaryKey();

        return $pk[0];
    }

    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute)
    {
        $model = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "'$attribute' => ['type' => TabularForm::INPUT_PASSWORD,'options' => ['placeholder' => ' ".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...']],";
                //return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "'$attribute' => ['type' => TabularForm::INPUT_TEXT, 'options' => ['placeholder' => '".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...']],";
                //return "\$form->field(\$model, '$attribute')";
            }
        }

        $column = $tableSchema->columns[$attribute];
        if(in_array($column->name,$this->hiddenFiledOptions)){
            return '';
        }
        elseif ($column->phpType === 'boolean') {
            //return "\$form->field(\$model, '$attribute')->checkbox()";
            return "'$attribute' => ['type' => Form::INPUT_CHECKBOX, 'options' => ['placeholder' => '".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...']],";
        } elseif ($column->type === 'text') {
            if(preg_match("/({$this->textEditorPregPart})$/i", $column->name)){
                return "'$attribute' => [
                    'type' => Form::INPUT_WIDGET,
                    'widgetClass' => '\\yii\\redactor\\widgets\\Redactor',
                    'clientOptions' => [
                        'imageManagerJson' => ['/redactor/upload/image-json'],
                        'imageUpload' => ['/redactor/upload/image'],
                        'fileUpload' => ['/redactor/upload/file'],
                        'lang' => 'zh_cn',
                        'plugins' => ['clips', 'fontcolor','imagemanager']
                    ]
                ],";
            }else{
                //return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
                return "'$attribute' => ['type' => Form::INPUT_TEXTAREA, 'options' => ['placeholder' => '".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...','rows' => 6]],";
            }
        } elseif ($column->type === 'smallint') {
            return "'$attribute' => ['type' => Form::INPUT_DROPDOWN_LIST,'items'=>['key1'=>'value1','key2'=>'value2'],'options' => ['prompt' => '".Yii::t('app', 'Select').' '."']],";
        } elseif ($column->type === 'integer') {
            if(preg_match("/(date)$/i", $column->name)){
                return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className(),'options' => ['type' => DateControl::FORMAT_DATE]],";
            }elseif(preg_match("/(datetime)$/i", $column->name)){
                return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className(),'options' => ['type' => DateControl::FORMAT_DATETIME]],";
            }else{
                return "'$attribute' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...']],";
            }
        } else {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'INPUT_PASSWORD';
            } else {
                $input = 'INPUT_TEXT';
            }
            if ($column->phpType !== 'string' || $column->size === null) {
                //return "\$form->field(\$model, '$attribute')->$input()";
                return "'$attribute' => ['type' => Form::".$input.", 'options' => ['placeholder' => '".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...']],";
            } else {
                if(preg_match("/({$this->uploadPregPart})$/i",$column->name)){
                    return "'$attribute' => [
                'type'=>Form::INPUT_WIDGET,
                'widgetClass'=>'\\kartik\\widgets\\FileInput',
                'options' => [
                    'pluginOptions'=>[
                        'showUpload' => false,
                        'showPreview' => false,
                    ]
                ],
            ],";
                }elseif(preg_match("/(time)$/i", $column->name)){
                    return "'$attribute' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className(),'options' => ['type' => DateControl::FORMAT_TIME]],";
                }elseif (preg_match("/^({$this->colorPregPart})$/i", $column->name)){
                    return "'$attribute'=>[
                        'type' => Form::INPUT_WIDGET,
                        'widgetClass' => '\\kartik\\widgets\\ColorInput'
                    ]";
                } else{
                    //return "\$form->field(\$model, '$attribute')->$input(['maxlength' => $column->size])";
                    return "'$attribute' => ['type' => Form::".$input.", 'options' => ['placeholder' => '".Yii::t('app', 'Enter').' '.$attributeLabels[$attribute]."...', 'maxlength' => ".$column->size."]],";
                }
            }
        }
    }

    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column)
    {
        if ($column->phpType === 'boolean') {
            return 'boolean';
        } elseif ($column->type === 'smallint') {
            return '';
        } else if ($column->type === 'integer') {
            if(preg_match("/(date|created_at|updated_at)$/i",$column->name)){
                return 'date';
            }elseif(preg_match("/(datetime)$/i",$column->name)){
                return 'datetime';
            }else{
                return '';
            }
        } elseif ($column->phpType !== 'string' || $column->size === null) {
            return '';
        } else {
            if(preg_match("/({$this->urlPregPart})$/i",$column->name)){
                return 'url';
            }elseif(stripos($column->name, 'email') !== false){
                return 'email';
            } else{
                return '';
            }
        }
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateSearchRules()
    {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    if(preg_match("/(date|datetime|created_at|updated_at)$/i",$column->name)){
                        $types['safe'][] = $column->name;
                    }else{
                        $types['integer'][] = $column->name;
                    }
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * @return array searchable attributes
     */
    public function getSearchAttributes()
    {
        return $this->getColumnNames();
    }

    /**
     * Generates the attribute labels for the search model.
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels()
    {
        /** @var \yii\base\Model $model */
        $model = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->getColumnNames() as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (strcasecmp(substr($label, -3), ' id') === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateSearchConditions()
    {
        $columns = [];
        $select_date_str = '';
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /** @var \yii\base\Model $model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                if(preg_match("/(date|datetime|created_at|updated_at)$/i",$column->name)){
                    if($column->name === 'created_at'){
                        $select_date_str.='
        //用户输入格式为YYYY-mm-dd
        if(!empty($this->created_at))
        {
            $date=$this->created_at;
            $date_arr=explode(\'-\',$date);
            if(count($date_arr) ==3 ){
                $stamp=mktime(0,0,0,intval($date_arr[1]),intval($date_arr[2]),intval($date_arr[0]));
                $created_time=$stamp;
                $created_end_time=$created_time+3600*24;
                $query->andFilterWhere([\'between\',$this->tableName().\'.created_at\',$created_time,$created_end_time]);
            }
        }
        ';
                    }
                    if($column->name === 'updated_at'){
                        $select_date_str.='
        //用户输入格式为YYYY-mm-dd
        if(!empty($this->updated_at))
        {
            $date=$this->updated_at;
            $date_arr=explode(\'-\',$date);
            if(count($date_arr) == 3){
                $stamp=mktime(0,0,0,intval($date_arr[1]),intval($date_arr[2]),intval($date_arr[0]));
                $updated_time=$stamp;
                $updated_end_time=$updated_time+3600*24;
                $query->andFilterWhere([\'between\',$this->tableName().\'.updated_at\',$updated_time,$updated_end_time]);
            }
        }
        ';
                    }
                    continue;
                }
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['like', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        $conditions[] = $select_date_str;
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            } else {
                return "'id' => \$model->{$pks[0]}";
            }
        } else {
            $params = [];
            foreach ($pks as $pk) {
                if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                    $params[] = "'$pk' => (string)\$model->$pk";
                } else {
                    $params[] = "'$pk' => \$model->$pk";
                }
            }

            return implode(', ', $params);
        }
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            return '$id';
        } else {
            return '$' . implode(', $', $pks);
        }
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (substr(strtolower($pk), -2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
            }

            return $params;
        }
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return boolean|\yii\db\TableSchema
     */
    public function getTableSchema()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * @return array model column names
     */
    public function getColumnNames()
    {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        } else {
            /** @var \yii\base\Model $model */
            $model = new $class();

            return $model->attributes();
        }
    }
}
