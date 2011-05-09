<?php

/**
 * GxController class file.
 *
 * @author Rodrigo Coelho <rodrigo@giix.org>
 * @link http://giix.org/
 * @copyright Copyright &copy; 2010-2011 Rodrigo Coelho
 * @license http://giix.org/license/ New BSD License
 */

/**
 * GxController is the base class for the generated controllers.
 *
 * @author Rodrigo Coelho <rodrigo@giix.org>
 * @since 1.0
 */
abstract class GxController extends Controller {

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
	 * Returns the data model based on the primary key or another attribute.
	 * This method is designed to work with the values passed via GET.
	 * If the data model is not found or there's a malformed key, an
	 * HTTP exception will be raised.
	 * @param mixed $key The key or keys of the model to be loaded.
	 * If the key is a string or an integer, the method will use the tables' PK if
	 * the PK has a single column. If the table has a composite PK and the key
	 * has a separator (see below), the method will detect it and use it.
	 * <pre>
	 * $key = '12-27'; // PK values with separator for tables with composite PK.
	 * </pre>
	 * If $key is an array, it can be indexed by integers or by attribute (column)
	 * names, as for {@link CActiveRecord::findByAttributes}.
	 * If the array doesn't have attribute names, as below, the method will use
	 * the table composite PK.
	 * <pre>
	 * $key = array(
	 *   12,
	 *   27,
	 *   ...,
	 * );
	 * </pre>
	 * If the array is indexed by attribute names, as below, the method will use
	 * the attribute names to search for and load the model.
	 * <pre>
	 * $key = array(
	 *   'model_id' => 44,
	 * 	 ...,
	 * );
	 * </pre>
	 * Warning: each attribute used should have an index on the database and the set of
	 * attributes used should identify only one item on the database (the attributes being
	 * ideally part of or multiple unique keys).
	 * @param string $modelClass The model class name.
	 * @return GxActiveRecord The loaded model.
	 * @see {@link GxActiveRecord::pkSeparator}.
	 * @throws CHttpException if there's an invalid request or if the model is not found.
	 */
	public function loadModel($key, $modelClass) {
		if (is_array($key)) {
			// The key is an array.
			// Check if there are column names indexing the values in the array.
			reset($key);
			if (key($key) === 0) {
				// There are no attribute names.
				// Check if there are multiple PK values. If there's only one, start again using only the value.
				if (count($key) === 1)
					return $this->loadModel($key[0], $modelClass);

				// Now we will use the composite PK.
				// Check if the table has composite PK.
				$tablePk = GxActiveRecord::model($modelClass)->getTableSchema()->primaryKey;
				if (!is_array($tablePk))
					throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));

				// Check if there are the correct number of keys.
				if (count($key) !== count($tablePk))
					throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));

				// Get an array of PK values indexed by the column names.
				$pk = GxActiveRecord::model($modelClass)->fillPkColumnNames($key);

				// Then load the model.
				$model = GxActiveRecord::model($modelClass)->findByPk($pk);
			} else {
				// There are attribute names.
				// Then we load the model now.
				$model = GxActiveRecord::model($modelClass)->findByAttributes($key);
			}
		} else {
			// The key is not an array.
			// Check if the table has composite PK.
			$tablePk = GxActiveRecord::model($modelClass)->getTableSchema()->primaryKey;
			if (is_array($tablePk)) {
				// The table has a composite PK.
				// The key must be a string to have a PK separator.
				if (!is_string($key))
					throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));

				// There must be a PK separator in the key.
				if (stripos($key, GxActiveRecord::$pkSeparator) === false)
					throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));

				// Generate an array, splitting by the separator.
				$keyValues = explode(GxActiveRecord::$pkSeparator, $key);

				// Start again using the array.
				return $this->loadModel($keyValues, $modelClass);
			} else {
				// The table has a single PK.
				// Then we load the model now.
				$model = GxActiveRecord::model($modelClass)->findByPk($key);
			}
		}

		// Check if we have a model.
		if ($model === null)
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));

		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel|array $model The model or array of models to be validated.
	 * @param string $form The name of the form. Optional.
	 */
	protected function performAjaxValidation($model, $form = null) {
		if (Yii::app()->request->isAjaxRequest && (($form === null) || ($_POST['ajax'] == $form))) {
			echo GxActiveForm::validate($model);
			Yii::app()->end();
		}
	}

	/**
	 * Finds the related primary keys specified in the form POST.
	 * Only for HAS_MANY and MANY_MANY relations.
	 * @param array $form The post data.
	 * @param array $relations A list of model relations in the format returned by {@link CActiveRecord::relations}.
	 * @return array An array where the keys are the relation names (string) and the values are arrays with the related model primary keys (int|string) or composite primary keys (array with pk name (string) => pk value (int|string)).
	 * Example of returned data:
	 * <pre>
	 * array(
	 *   'categories' => array(1, 4),
	 *   'tags' => array(array('id1' => 3, 'id2' => 7), array('id1' => 2, 'id2' => 0)) // composite pks
	 * )
	 * </pre>
	 * An empty array is returned in case there is no related pk data from the post.
	 * This data comes directly from the form POST data.
	 */
	protected function getRelatedData($form, $relations) {
		$relatedPk = array();
		foreach ($relations as $relationName => $relationData) {
			if (isset($form[$relationName]) && (($relationData[0] == GxActiveRecord::HAS_MANY) || ($relationData[0] == GxActiveRecord::MANY_MANY)))
				$relatedPk[$relationName] = $form[$relationName] === '' ? null : $form[$relationName];
		}
		return $relatedPk;
	}

}