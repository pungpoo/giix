<?php
/**
 * This is the template for generating the model class of a specified table.
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 * - $representingColumn: the name of the representing column for the table (string) or
 *   the names of the representing columns (array)
 */
?>
<?php echo "<?php\n"; ?>

/**
 * This is the model base class for the table "<?php echo $tableName; ?>".
 * DO NOT MODIFY THIS FILE! It is automatically generated by giix.
 * If any changes are necessary, you must set or override the required
 * property or method in class "<?php echo $modelClass; ?>".
 *
 * Columns in table "<?php echo $tableName; ?>" available as properties of the model,
<?php if(count($relations)>0): ?>
 * followed by relations of table "<?php echo $tableName; ?>" available as properties of the model.
<?php else: ?>
 * and there are no model relations.
<?php endif; ?>
 *
<?php foreach($columns as $column): ?>
 * @property <?php echo $column->type.' $'.$column->name."\n"; ?>
<?php endforeach; ?>
 *
<?php foreach($relations as $name=>$relation): ?>
 * @property <?php
	if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
	{
		$relationType = $matches[1];
		$relationModel = $matches[2];

		switch($relationType){
			case 'HAS_ONE':
				echo $relationModel.' $'.$name."\n";
				break;
			case 'BELONGS_TO':
				echo $relationModel.' $'.$name."\n";
				break;
			case 'HAS_MANY':
				echo $relationModel.'[] $'.$name."\n";
				break;
			case 'MANY_MANY':
				echo $relationModel.'[] $'.$name."\n";
				break;
			default:
				echo 'mixed $'.$name."\n";
		}
	}
	?>
<?php endforeach; ?>
 */
abstract class <?php echo $this->baseModelClass; ?> extends <?php echo $this->baseClass; ?> {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '<?php echo $tableName; ?>';
	}

	public static function representingColumn() {
<?php if (is_array($representingColumn)): ?>
		return array(
<?php foreach($representingColumn as $representingColumn_item): ?>
			'<?php echo $representingColumn_item; ?>',
<?php endforeach; ?>
		);
<?php else: ?>
		return '<?php echo $representingColumn; ?>';
<?php endif; ?>
	}

	public function rules() {
		return array(
<?php foreach($rules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach; ?>
			array('<?php echo implode(', ', array_keys($columns)); ?>', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
<?php foreach($relations as $name=>$relation): ?>
			<?php echo "'{$name}' => {$relation},\n"; ?>
<?php endforeach; ?>
		);
	}

	public function attributeLabels() {
		return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'{$name}' => Yii::t('app', '{$label}'),\n"; ?>
<?php endforeach; ?>
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

<?php foreach($columns as $name=>$column): ?>
<?php $partial = ($column->type==='string' and !$column->isForeignKey); ?>
		$criteria->compare('<?php echo $name; ?>', $this-><?php echo $name; ?><?php echo $partial ? ', true' : ''; ?>);
<?php endforeach; ?>

		return new CActiveDataProvider(get_class($this), array(
			'criteria' => $criteria,
		));
	}
}