<?php

/**
 * GxActiveRecord class file.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @link http://rodrigocoelho.com.br/giix/
 * @copyright Copyright &copy; 2010 Rodrigo Coelho
 * @license http://rodrigocoelho.com.br/giix/license/ New BSD License
 */

/**
 * GxActiveRecord is the base class for the generated AR (base) models.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @since 1.0
 */
abstract class GxActiveRecord extends CActiveRecord {

	/**
	 * @var string the separator used to separate the primary keys values in a
	 * composite pk table. Usually a character.
	 */
	public static $pkSeparator = '-';

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * The specified column(s) is(are) the responsible for the
	 * string representation of the model instance.
	 * The column is used in the {@link __toString} default implementation.
	 * Every model must specify the attributes used to build their
	 * string representation by overriding this method.
	 * This method must be overriden in each model class
	 * that extends this class.
	 * @return string|array the name of the representing column for the table (string) or
	 * the names of the representing columns (array).
	 * @see {@link __toString}.
	 */
	public static function representingColumn() {
		return null;
	}

	/**
	 * Returns a string representation of the model instance, based on
	 * {@link representingColumn}.
	 * If the representing column is not set, the primary key will be used.
	 * If there is no primary key, the first field will be used.
	 * When you overwrite this method, all model attributes used to build
	 * the string representation of the model must be specified in
	 * {@link representingColumn}.
	 * @return string the string representation for the model instance.
	 */
	public function __toString() {
		$representingColumn = $this->representingColumn();

		if ($representingColumn === null)
			if ($this->getTableSchema()->primaryKey !== null)
				$representingColumn = $this->getTableSchema()->primaryKey;
			else
				$representingColumn=$this->getTableSchema()->columnNames[0];

		if (is_array($representingColumn)) {
			$part = '';
			foreach ($representingColumn as $representingColumn_item) {
				$part .= ( $this->$representingColumn_item === null ? '' : $this->$representingColumn_item) . '-';
			}
			return substr($part, 0, -1);
		} else {
			return $this->$representingColumn === null ? '' : (string) $this->$representingColumn;
		}
	}

	/**
	 * Finds all active records satisfying the specified condition, selecting only the requested
	 * attributes and, if specified, the primary keys.
	 * See {@link CActiveRecord::find} for detailed explanation about $condition and $params.
	 * @param string|array $attributes the names of the attributes to be selected.
	 * Optional. If not specified, the {@link representingColumn} will be used.
	 * @param boolean $withPk specifies if the primary keys will be selected.
	 * @param mixed $condition query condition or criteria.
	 * @param array $params parameters to be bound to an SQL statement.
	 * @return array list of active records satisfying the specified condition. An empty array is returned if none is found.
	 */
	public function findAllAttributes($attributes = null, $withPk = false, $condition='', $params=array()) {
		$criteria = $this->getCommandBuilder()->createCriteria($condition, $params);
		if ($attributes === null)
			$attributes = $this->representingColumn();
		if ($withPk) {
			$pks = self::model(get_class($this))->tableSchema->primaryKey;
			if (!is_array($pks))
				$pks = array($pks);
			if (!is_array($attributes))
				$attributes = array($attributes);
			$attributes = array_merge($pks, $attributes);
		}
		$criteria->select = $attributes;
		return parent::findAll($criteria);
	}

	/**
	 * Extracts and returns only the primary keys values from each model.
	 * @param CActiveRecord|array $model a model or an array of models.
	 * @param boolean $forceString whether pk values on composite pk tables
	 * should be compressed into a string. The values on the string will by
	 * separated by {@link $pkSeparator}.
	 * @return string|array the pk value as a string (for single pk tables) or
	 * array (for composite pk tables) if one model was specified or
	 * an array of strings or arrays if multiple models were specified.
	 */
	public static function extractPkValue($model, $forceString = false) {
		if (!is_array($model)) {
			$pk = $model->primaryKey;
			if ($forceString && is_array($pk))
				$pk = implode(self::$pkSeparator, $pk);
			return $pk;
		} else {
			$pks = array();
			foreach ($model as $model_item) {
				$pks[] = self::extractPkValue($model_item, $forceString);
			}
			return $pks;
		}
	}

}