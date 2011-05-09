<?php

/**
 * GxActiveForm class file.
 *
 * @author Rodrigo Coelho <rodrigo@giix.org>
 * @link http://giix.org/
 * @copyright Copyright &copy; 2010-2011 Rodrigo Coelho
 * @license http://giix.org/license/ New BSD License
 */

/**
 * GxActiveForm provides forms with additional features.
 *
 * @author Rodrigo Coelho <rodrigo@giix.org>
 * @since 1.0
 */
class GxActiveForm extends CActiveForm {

	/**
	 * Renders a checkbox list for a model attribute.
	 * This method is a wrapper of {@link GxHtml::activeCheckBoxList}.
	 * Overrides and based on {@link CActiveForm::checkBoxList}.
	 * Changes: uses GxHtml.
	 * @see {@link CActiveForm::checkBoxList} for more information.
	 * @param CModel $model The data model.
	 * @param string $attribute The attribute.
	 * @param array $data Value-label pairs used to generate the check box list.
	 * @param array $htmlOptions Addtional HTML options.
	 * @return string The generated check box list.
	 */
	public function checkBoxList($model, $attribute, $data, $htmlOptions = array()) {
		return GxHtml::activeCheckBoxList($model, $attribute, $data, $htmlOptions);
	}

}