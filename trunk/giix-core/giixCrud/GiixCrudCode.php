<?php

/**
 * GiixCrudCode class file.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @link http://rodrigocoelho.com.br/giix/
 * @copyright Copyright &copy; 2010 Rodrigo Coelho
 * @license http://rodrigocoelho.com.br/giix/license/ New BSD License
 */
Yii::import('system.gii.generators.crud.CrudCode');
Yii::import('ext.giix-core.helpers.*');

/**
 * GiixCrudCode is the model for giix crud generator.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @since 1.0
 */
class GiixCrudCode extends CrudCode {

	/**
	 * @var string The type of authentication.
	 */
	public $authtype = 'auth_none';
	/**
	 * @var int Specifies if ajax validation is enabled. 0 represents false, 1 represents true.
	 */
	public $enable_ajax_validation = 0;
	/**
	 * @var string The controller base class name.
	 */
	public $baseControllerClass = 'GxController';

	/**
	 * Adds the new model attributes (class properties) to the rules.
	 * Overrides CrudCode::rules.
	 */
	public function rules() {
		return array_merge(parent::rules(), array(
			array('authtype, enable_ajax_validation', 'required'),
		));
	}

	/**
	 * Sets the labels for the new model attributes (class properties).
	 * Overrides CrudCode::attributeLabels.
	 */
	public function attributeLabels() {
		return array_merge(parent::attributeLabels(), array(
			'authtype' => 'Authentication type',
			'enable_ajax_validateion' => 'Enable ajax Validation',
		));
	}

	/**
	 * Generates and returns the view source code line
	 * to create the appropriate active input field based on
	 * the model attribute field type on the database.
	 * Overrides CrudCode::generateActiveField.
	 * @param string $modelClass The model class name.
	 * @param CDbColumnSchema $column The column.
	 * @return string The source code line for the active field.
	 */
	public function generateActiveField($modelClass, $column) {
		if ($column->isForeignKey) {
			$relation = $this->findRelation($modelClass, $column);
			$relatedModelClass = $relation[3];
			return "echo \$form->dropDownList(\$model, '{$column->name}', GxHtml::listDataEx({$relatedModelClass}::model()->findAllAttributes(null, true)))";
		}

		if (strtoupper($column->dbType) == 'TINYINT(1)'
				|| strtoupper($column->dbType) == 'BIT'
				|| strtoupper($column->dbType) == 'BOOL'
				|| strtoupper($column->dbType) == 'BOOLEAN') {
			return "echo \$form->checkBox(\$model, '{$column->name}')";
		} else if (strtoupper($column->dbType) == 'DATE') {
			return "\$form->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model' => \$model,
			'attribute' => '{$column->name}',
			'value' => \$model->{$column->name},
			'options' => array(
				'showButtonPanel' => true,
				'changeYear' => true,
				'dateFormat' => 'yy-mm-dd',
				),
			));\n";
		} else if (substr(strtoupper($column->dbType), 0, 4) == 'ENUM') {
			$result = "echo \$form->dropDownList(\$model, '{$column->name}', array(\n";

			$enum_values = explode(',', substr($column->dbType, 4, strlen($column->dbType) - 1));
			foreach ($enum_values as $enum_value) {
				$enum_value = trim($enum_value, "()'");
				$result .= "\t\t\t'{$data[$enum_value]}' => Yii::t('app', {$enum_value}),\n";
			}
			$result .= "))";
			return $result;
		} else {
			return 'echo ' . parent::generateActiveField($modelClass, $column);
		}
	}

	/**
	 * Generates and returns the view source code line
	 * to create the appropriate active input field based on
	 * the model relation.
	 * @param string $modelClass The model class name.
	 * @param array $relation The relation details in the same format
	 * used by {@link getRelations()}.
	 * @return string The source code line for the relation field.
	 */
	public function generateActiveRelationField($modelClass, $relation) {
		$relationName = $relation[0];
		$relationType = $relation[1];
		$relationField = $relation[2]; // The FK.
		$relationModel = $relation[3];
		// The relation type must be HAS_ONE, HAS_MANY or MANY_MANY.
		// Other types (BELONGS_TO) should be generated by generateActiveField.
		if ($relationType != CActiveRecord::HAS_ONE && $relationType != CActiveRecord::HAS_MANY && $relationType != CActiveRecord::MANY_MANY)
			throw new InvalidArgumentException('The argument $relationName must have a relation type of HAS_ONE, HAS_MANY or MANY_MANY.');

		// Generate the field according to the relation type.
		switch ($relationType) {
			case CActiveRecord::HAS_ONE:
				return "echo \$form->dropDownList(\$model, '{$relationName}', GxHtml::listDataEx({$relationModel}::model()->findAllAttributes(null, true)))";
				break;
			case CActiveRecord::HAS_MANY:
			case CActiveRecord::MANY_MANY:
				return "echo \$form->checkBoxList(\$model, '{$relationName}', GxHtml::listDataEx({$relationModel}::model()->findAllAttributes(null, true)))";
				break;
		}
	}

	public function generateInputField($modelClass, $column) {
		return 'echo ' . parent::generateInputField($modelClass, $column);
	}

	/**
	 * Generates and returns the view source code line
	 * to create the appropriate attribute configuration for a CDetailView.
	 * @param string $modelClass The model class name.
	 * @param CDbColumnSchema $column The column.
	 * @return string The source code line for the attribute.
	 */
	public function generateDetailViewAttribute($modelClass, $column) {
		if (!$column->isForeignKey)
			return "'{$column->name}'";
		else {
			// Find the relation name for this column.
			$relation = $this->findRelation($modelClass, $column);
			$relationName = $relation[0];
			$relatedModelClass = $relation[3];
			$relatedControllerName = strtolower($relatedModelClass[0]) . substr($relatedModelClass, 1);

			return "array(
			'label' => '{$relatedModelClass}',
			'type' => 'raw',
			'value' => GxHtml::link(GxHtml::encode(GxHtml::valueEx(\$model->{$relationName})), array('{$relatedControllerName}/view', 'id' => GxActiveRecord::extractPkValue(\$model->{$relationName}, true))),
			)";
		}
	}

	/**
	 * Generates and returns the view source code line
	 * to create the CGridView column definition.
	 * @param string $modelClass The model class name.
	 * @param CDbColumnSchema $column The column.
	 * @return string The source code line for the column definition.
	 */
	public function generateGridViewColumn($modelClass, $column) {
		if (!$column->isForeignKey) {
			// Boolean or bit.
			if (strtoupper($column->dbType) == 'TINYINT(1)'
					|| strtoupper($column->dbType) == 'BIT'
					|| strtoupper($column->dbType) == 'BOOL'
					|| strtoupper($column->dbType) == 'BOOLEAN') {
				return "array(
					'name' => '{$column->name}',
					'value' => '(\$data->{$column->name} === 0) ? Yii::t(\\'app\\', \\'No\\') : Yii::t(\\'app\\', \\'Yes\\')',
					'filter' => array('0' => Yii::t('app', 'No'), '1' => Yii::t('app', 'Yes')),
					)";
			} else // Common column.
				return "'{$column->name}'";
		} else { // FK.
			// Find the related model for this column.
			$relation = $this->findRelation($modelClass, $column);
			$relationName = $relation[0];
			$relatedModelClass = $relation[3];
			return "array(
				'name'=>'{$column->name}',
				'value'=>'GxHtml::valueEx(\$data->{$relationName})',
				'filter'=>GxHtml::listDataEx({$relatedModelClass}::model()->findAllAttributes(null, true)),
				)";
		}
	}

	/**
	 * Generates and returns the view source code line
	 * to create the advanced search.
	 * @param string $modelClass The model class name.
	 * @param CDbColumnSchema $column The column.
	 * @return string The source code line for the column definition.
	 */
	public function generateSearchField($modelClass, $column) {
		if (!$column->isForeignKey) {
			// Boolean or bit.
			if (strtoupper($column->dbType) == 'TINYINT(1)'
					|| strtoupper($column->dbType) == 'BIT'
					|| strtoupper($column->dbType) == 'BOOL'
					|| strtoupper($column->dbType) == 'BOOLEAN')
				return "echo \$form->dropDownList(\$model, '{$column->name}', array('0' => Yii::t('app', 'No'), '1' => Yii::t('app', 'Yes')), array('prompt' => Yii::t('app', 'All')))";
			else // Common column.
				return $this->generateActiveField($this->modelClass, $column); // Will add 'echo' when necessary.

		} else { // FK.
			// Find the related model for this column.
			$relation = $this->findRelation($modelClass, $column);
			$relatedModelClass = $relation[3];
			return "echo \$form->dropDownList(\$model, '{$column->name}', GxHtml::listDataEx({$relatedModelClass}::model()->findAllAttributes(null, true)), array('prompt' => Yii::t('app', 'All')))";
		}
	}

	/**
	 * Finds the relation of the specified column.
	 * @param string $modelClass The model class name.
	 * @param CDbColumnSchema $column The column.
	 * @return array The relation. The array will have 3 values:
	 * 0: the relation name,
	 * 1: the relation type (will always be CActiveRecord::BELONGS_TO),
	 * 2: the foreign key (will always be the specified column),
	 * 3: the related active record class name.
	 * Or null if no matching relation was found.
	 */
	public function findRelation($modelClass, $column) {
		if (!$column->isForeignKey)
			return null;
		$relations = GxActiveRecord::model($modelClass)->relations();
		// Find the relation for this attribute.
		foreach ($relations as $relationName => $relation) {
			// For attributes on this model, relation must be BELONGS_TO.
			if ($relation[0] == GxActiveRecord::BELONGS_TO && $relation[2] == $column->name) {
				return array(
					$relationName, // the relation name
					$relation[0], // the relation type
					$relation[2], // the foreign key
					$relation[1] // the related active record class name
				);
			}
		}
		// None found.
		return null;
	}

	/**
	 * Returns all the relations of the specified model.
	 * @param string $modelClass The model class name.
	 * @return array The relations. Each array item is
	 * a relation as an array, having 3 items:
	 * 0: the relation name,
	 * 1: the relation type,
	 * 2: the foreign key,
	 * 3: the related active record class name.
	 * Or an empty array if no relations were found.
	 */
	public function getRelations($modelClass) {
		$relations = GxActiveRecord::model($modelClass)->relations();
		$result = array();
		foreach ($relations as $relationName => $relation) {
			$result[] = array(
				$relationName, // the relation name
				$relation[0], // the relation type
				$relation[2], // the foreign key
				$relation[1] // the related active record class name
			);
		}
		return $result;
	}

}