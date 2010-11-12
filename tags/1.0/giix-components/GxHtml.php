<?php

/**
 * GxHtml class file.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @link http://rodrigocoelho.com.br/giix/
 * @copyright Copyright &copy; 2010 Rodrigo Coelho
 * @license http://rodrigocoelho.com.br/giix/license/ New BSD License
 */

/**
 * GxHtml extends CHtml and provides additional features.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @since 1.0
 */
class GxHtml extends CHtml {

	/**
	 * Renders a checkbox list for a model attribute.
	 * Overrides and based on {@link CHtml::activeCheckBoxList}.
	 * Changes: added support to HAS_MANY and MANY_MANY relations.
	 * @see {@link CHtml::activeCheckBoxList} for more information.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the check box list.
	 * @param array $htmlOptions addtional HTML options.
	 * @return string the generated check box list
	 */
	public static function activeCheckBoxList($model, $attribute, $data, $htmlOptions = array()) {
		self::resolveNameID($model, $attribute, $htmlOptions);
		$selection = self::selectData(self::resolveValue($model, $attribute));
		if ($model->hasErrors($attribute))
			self::addErrorCss($htmlOptions);
		$name = $htmlOptions['name'];
		unset($htmlOptions['name']);

		$hiddenOptions = isset($htmlOptions['id']) ? array('id' => self::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
		return self::hiddenField($name, '', $hiddenOptions)
		. self::checkBoxList($name, $selection, $data, $htmlOptions);
	}

	/**
	 * Generates the data suitable for list-based HTML elements.
	 * This method is based on {@link CHtml::listData}.
	 * Changes: this method supports {@link GxActiveRecord::representingColumn()} and {@link GxActiveRecord::toString()} and
	 * tables with composite primary keys.
	 * @see {@link CHtml::listData} for more information.
	 * @param array $models a list of model objects. Starting from version 1.0.3, this parameter
	 * can also be an array of associative arrays (e.g. results of {@link CDbCommand::queryAll}).
	 * @param string $valueField the attribute name for list option values.
	 * Optional. If not specified, the primary key (or keys) will be used.
	 * @param string $textField the attribute name for list option texts
	 * Optional. If not specified, the {@link GxActiveRecord::__toString()} method will be used.
	 * @param string $groupField the attribute name for list option group names. If empty, no group will be generated.
	 * @return array the list data that can be used in {@link dropDownList}, {@link listBox}, etc.
	 */
	public static function listDataEx($models, $valueField = null, $textField = null, $groupField = '') {
		$listData = array();
		if ($groupField === '') {
			foreach ($models as $model) {
				// If $valueField is null, use the primary key.
				if ($valueField === null)
					$value = GxActiveRecord::extractPkValue($model, true);
				else
					$value = self::valueEx($model, $valueField);
				$text = self::valueEx($model, $textField);
				$listData[$value] = $text;
			}
		} else {
			foreach ($models as $model) {
				// If $valueField is null, use the primary key.
				if ($valueField === null)
					$value = GxActiveRecord::extractPkValue($model, true);
				else
					$value = self::valueEx($model, $valueField);
				$group = self::valueEx($model, $groupField);
				$text = self::valueEx($model, $textField);
				$listData[$group][$value] = $text;
			}
		}
		return $listData;
	}

	/**
	 * Generates the select data suitable for list-based HTML elements.
	 * The select data has the attribute or related data as returned
	 * by {@link CHtml::resolveValue}.
	 * If the select data comes from a MANY_MANY or a HAS_MANY related
	 * attribute (is a model or an array of models), it is transformed
	 * to a string or an array of strings with the selected primary keys.
	 * @param mixed $value the value of the attribute as returned by
	 * {@link CHtml::resolveValue}.
	 */
	public static function selectData($value) {
		// If $value is a model or an array of models, turn it into
		// a string or an array of strings with the pk values.
		if ((is_object($value) && is_subclass_of($value, 'GxActiveRecord')) ||
				(is_array($value) && count($value) > 0 && is_object($value[0]) && is_subclass_of($value[0], 'GxActiveRecord')))
			return GxActiveRecord::extractPkValue($value, true);
		else
			return $value;
	}

	/**
	 * Evaluates the value of the specified attribute for the given model.
	 * This method is based on {@link CHtml::value}.
	 * Changes: this method supports {@link GxActiveRecord::representingColumn()} and {@link GxActiveRecord::toString()}.
	 * @see {@link CHtml::value} for more information.
	 * @param mixed $model the model. This can be either an object or an array.
	 * @param string $attribute the attribute name (use dot to concatenate multiple attributes).
	 * Optional. If not specified, the {@link GxActiveRecord::__toString()} method will be used.
	 * In this case, the fist parameter ($model) can not be an array, it must be an instance of GxActiveRecord.
	 * @param mixed $defaultValue the default value to return when the attribute does not exist
	 * @return mixed the attribute value
	 */
	public static function valueEx($model, $attribute = null, $defaultValue = null) {
		if ($attribute === null)
			return $model->__toString(); // It is assumed that $model is an instance of GxActiveRecord.

			foreach (explode('.', $attribute) as $name) {
			if (is_object($model))
				$model = $model->$name;
			else if (is_array($model) && isset($model[$name]))
				$model = $model[$name];
			else
				return $defaultValue;
		}
		return $model;
	}

}