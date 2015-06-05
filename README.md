# yii2-toolbox
Yii2-Toolbox is a collection of useful helpers, widgets etc. extending the basic functionality of Yii2


## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

	"asinfotrack/yii2-toolbox": "dev-master"


## Contents


### Components

###### PdfResponseFormatter
Additional response formatter for handling PDF-requests. You need to add the formatter to the config like this:
```php
'response' => [
	// ...
	'formatters'=>[
		'pdf'=>'asinfotrack\yii2\toolbox\components\PdfResponseFormatter',
		'pdf_download'=>[
			'class'=>'asinfotrack\yii2\toolbox\components\PdfResponseFormatter', 
			'forceDownload'=>true,
		],
	],
	// ...
],
```

After that you can use it to output PDFs via actions easily:

```php
public function actionPdf()
{
	//create pdf with some library (eg FPDF)
	$pdf = new FPDF();
	// ...

	$response = Yii::$app->response;
	$response->data = $pdf->Output();
	$response->format = 'pdf';
}
```

###### User
Extends `\yii\web\User` with the ability to check multiple rights at once (canAll, 
canAny, canOne).



### Widgets

###### Button
The button-widget extends the one provided by yii2. It adds functionality to specify an icon.
The Icons depend on font-awesome and hence require the yii2-extension `rmrevin/yii2-fontawesome`

###### Panel
Renders a Bootstrap-Panel. You can either set its body via attribute or between `begin()` and `end()`.
The attributes `heading`, `body` and `footer` support setting via string or via Closure returning a string.
Exemplary usage:

```php
<?php Panel::begin([
	'heading'=>Html::tag('h3', 'Welcome!'),
	'footer'=>function() {
		return Yii::$app->formatter->asDatetime(time());
	},
]); ?>
 	
 	<p>Hello world! This is a simple panel with a heading.</p>
 	 	
<?php Penel::end(); ?>
```

###### Grid column-types
The column types provided extend the functionality of the basic `\yii\grid\DataColumn`. The
class is `AdvancedDataColumn`. It has functionality to align text, set the column with with either
absolute or percent values etc.

Additionally there are three further column types:
* `BooleanColumn` optimized for rendering boolean values
*  `IdColumn` optimized for rendering id values eith with or without code-tags
* `LinkColumn` renders links (which can be generated using a closure)


### Helpers

###### Color
Makes working with HEX- or RGB-colors easy! It can translate between the two formats,
lighten or darken colors as well as creating steps between two colors. You can also
use it to get a colors luminance or validate color-values.
Short HEX-Formats are supported automatically.

###### Html
Extends the Html-helper of Yii2 with additional functionality

###### ServerConfig
Provides functionality to fetch the most important server vars and to check if certain
extensions are loaded

###### Timestamp
This helper is responsible for common tasks associated with UNIX-Timestamps. It also has a
function to parse dates into timestamps (extracted functionality of the yii date validator)

###### Url
This helper extends the basic functionality of the Yii2-Url-helper. It provides functionality
to retrieve information about the currently requested url, such as TLD, subdomains, etc.


### Behaviors

###### ArchiveBehavior / ArchiveQueryBehavior
Enables a model to be archived or not. This is similiar to a soft-delete but with the idea of not
deleting a record but archive it instead.
The behavior is fully configurable and there is also a behavior for the corresponding query-class.



### Validators

###### SelectiveRequiredValidator
Validator to require a certain amount of fields out of a list to be required. To use the validator
simply specify the selection of attributes and set how many of it are required:

```php
public function rules()
{
    return [
        // ...
        [['phonePrivate','phoneWork','mobile'], SelectiveRequiredValidator::className(), 'errorAttribute'=>'phonePrivate'],
        // ...
    ];
}
```

### Exceptions

###### ExpiredHttpException
Throws code 410 marking a link as expired. This is helpful if for example a meeting is over or
a record was archived.


### Gii-Generators
The provided Gii-Generators fix general formatting issues in the default code-templates (eg
spaces instead of tabs, etc.).

###### Installation
To enable the provided Generators you need to update the gii config as follows:
```php
'modules'=>[
	// ...
	'gii'=>[
		'class'=>'yii\gii\Module',
		'generators'=>[
			'model'=>[
				'class'=>'asinfotrack\yii2\toolbox\gii\model\Generator',
				'templates'=>[
					'asiToolboxModel'=>'@vendor/asinfotrack/yii2-toolbox/gii/model/default',
				],
			],
			'crud'=>[
				'class'=>'asinfotrack\yii2\toolbox\gii\crud\Generator',
				'templates'=>[
					'asiToolboxCrud'=>'@vendor/asinfotrack/yii2-toolbox/gii/crud/default',				
				],
			],
		],
	],
	// ...
],
```

###### Models
Timestamp- and BlameableBehaviour are added by default. Also there is an optional font-awesome
icon-name assignable per model. This can later be retrieved via `Model::iconName()`.
Also there is a query-class generated by default.

###### CRUDs
The CRUD-templates also fix common issues and are optimized for the model
