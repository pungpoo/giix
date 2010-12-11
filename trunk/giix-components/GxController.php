<?php

/**
 * GxController class file.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @link http://rodrigocoelho.com.br/giix/
 * @copyright Copyright &copy; 2010 Rodrigo Coelho
 * @license http://rodrigocoelho.com.br/giix/license/ New BSD License
 */

/**
 * GxController is the base class for the generated controllers.
 *
 * @author Rodrigo Coelho <giix@rodrigocoelho.com.br>
 * @since 1.0
 */
abstract class GxController extends CController {

	/**
	 * @var string The layout for the controller view.
	 */
	public $layout = '//layouts/column2';
	/**
	 * @var array Context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu = array();
	/**
	 * @var array The breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs = array();

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param mixed $id the ID of the model to be loaded
	 * @param string $modelClass the model class name
	 * @return GxActiveRecord the loaded model
	 */
	public function loadModel($id, $modelClass) {
		$model = GxActiveRecord::model($modelClass)->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel $model the model to be validated
	 * @param string $form the name of the form
	 */
	protected function performAjaxValidation($model, $form) {
		if (Yii::app()->request->isAjaxRequest && $_POST['ajax'] == $form) {
			echo GxActiveForm::validate($model);
			Yii::app()->end();
		}
	}

}