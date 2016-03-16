# giix #

**New home at Github**: https://github.com/rcoelho/giix

giix is gii Extended, a code generator for Yii PHP framework.

giix is inspired and based on gii-template-collection (gtc), by Herbert Maschke (thyseus).

giix is free software and is dual-licensed under the terms of the new BSD License and under the terms of GNU GPL v3. See the LICENSE file.

### Links ###

[giix extension page](http://www.yiiframework.com/extension/giix)

[Forum discussion for giix](http://www.yiiframework.com/forum/index.php?/topic/13154-giix-%E2%80%94-gii-extended/)

### Acknowledgements ###

giix is inspired and uses code from Yii PHP framework and gii-template-collection. Many thanks to Qiang Xue, Herbert Maschke and the contributors of these software.

### Features ###

giix extends Yii's gii by providing:

  * Proper handling of related model attributes, rendering appropriate form fields based on relation type.
  * More support for HAS\_MANY and MANY\_MANY relations.
  * Native support for saving MANY\_MANY relations with the new method GxActiveRecord::saveWithRelated.
  * Native support for saving multiple (related or not) records with the new method GxActiveRecord::saveMultiple.
  * Automatic string representation for a model via GxActiveRecord::representingColumn() and GxActiveRecord::toString().
  * Out-of-the box i18n support by using Yii::t().
  * Appropriate form fields are rendered based on model attribute/table field data type.
  * Separated model and basemodel. Basemodel can be regenerated without overwriting your code in the model.
  * Smart methods can query your database for just the needed data (usually the primary key and the field with the string representantion), avoiding manual setup or a "select `*`".
  * Extensive use of default method parameters. Appropriate data is found automatically.
  * Some (incipient) support for tables with composite primary keys.
  * Generated code is completely free from styling and formatting. Your CSS controls the presentation.
  * Generated code is (almost) free of comments.
  * Well documented and commented source code. Ohloh says that 40% of the lines in the code are comments! You can understand and modify anything you want.

And a lot more! Read the CHANGELOG file and the (richly commented) source code to fully leverage giix's power.

Some of these features come from gtc.

### Warnings ###

giix is not fully tested now, so please test your application and be careful using it.
giix is not production ready yet.

giix is still in development. Some changes may break backwards compatibility.

### Installation and upgrading ###

Please see [INSTALL](http://giix.googlecode.com/svn/trunk/INSTALL) and [UPGRADE](http://giix.googlecode.com/svn/trunk/UPGRADE) files for instructions.

Please check the [README](http://giix.googlecode.com/svn/trunk/README) file for more information.

