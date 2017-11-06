# yii2-toolbox
Yii2-Toolbox is a collection of useful helpers, widgets etc. extending the basic functionality of Yii2


## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

	"asinfotrack/yii2-toolbox": "~0.8.6"

## Changelog

###### [v0.8.7] (work in progress)

- Bugfix in archive behavior where already set archived value would be overwritten during insert

###### [v0.8.6](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.6)

- Bugfix with ArchiveBehavior where table-name was missing (lead to conflicts with relations)

###### [v0.8.5](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.5)

- General-dependency update
- Major cleanup
- Updated Gii-templates and generators

###### [v0.8.4](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.4)

- Video-widget added

###### [v0.8.3](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.3)

- General-dependency update and adding image-functionality

###### [v0.8.2](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.2)

- Resolved compatibility issue of `AdvancedActionColumn` when template was specified as a callback with Yii 2.0.11

###### [v0.8.1](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.1)

- Geocoding helper added
- Query helper added
- Url helper extended with `isInIpRange()`-method

###### [v0.8.0](https://github.com/asinfotrack/yii2-toolbox/releases/tag/0.8.0)

- first stable release


## Contents


### Components

###### MemoryUrlManager
This URL manager implements memory-functionality for urls. This enables for example to keep
the state (sorting, filtering and paging) of a GridView across requests. The default configuration
saves this data in the session variable and appends the params to the links.

The usage is very easy. Simply set this class as the url manager in your Yii-config and specify the
additional attributes according to the example below and the documentation within the class.

Example of a config:
```php
// ...
'urlManager'=>[
	'class'=>'\asinfotrack\yii2\toolbox\components\MemoryUrlManager',
	
	'memoryMap'=>[

		//the controller
		'mycontroller'=>[
			//the action
			'myindexaction'=>[
				//regex rules...if a param matches a rule it will be memorized
				'/^SearchForm/',
				//if a rule is specified like this, the regex is only enabled if the callback returns true
				'page'=>function() {
					return rand(0,1) == 1;
				},
			],
		],

		//modules work the same, except they have one level more
		'mymodule'=>[
			'mymodulecontroller'=>[
				//the action
				'mymoduleaction'=>[
					//regex rules...if a param matches a rule it will be memorized
					'/^MyForm/',
				],
			],
		],

	],
],
// ...
```

Each entry in the `memoryMap` can be a string representing a regex to match params to save. You can
optionally use the regex-rule as key and a callback returning a boolean as the value. In this case
the rule is only active when the callback returns true

###### ImageResponseFormatter
Response formatter for images. You need to add the formatter to the config as follows:
```php
'response' => [
	// ...
	'formatters'=>[
		'img_jpg'=>[
			'class'=>'asinfotrack\yii2\toolbox\components\ImageResponseFormatter',
			'extension'=>'jpg',
		],
		'img_png'=>[
			'class'=>'asinfotrack\yii2\toolbox\components\ImageResponseFormatter',
			'extension'=>'png',
		],
		'img_gif'=>[
			'class'=>'asinfotrack\yii2\toolbox\components\ImageResponseFormatter',
			'extension'=>'gif',
		],
	],
	// ...
],
```

After that you can use it to output images via actions easily:

```php
public function actionAvatar()
{	
	Yii::$app->response->format = 'img_png';
	return file_get_contents($pathToMyImage);
}
```

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

###### ProgressiveImageGd and ProgressiveImageImagick
Both classes extend the image drivers of `yurkinx/yii2-image` to enable progressive encoding of jpg-files.
To use it, simply call the factory-class `asinfotrack\yii2\toolbox\helpers\ImageFactory::createInstance($path, $driver=self::DRIVER_GD)`
to get an instance and proceed working. Make sure either GD- or Imagick-Library is enabled.


### Widgets

###### Button
The button-widget extends the one provided by yii2. It adds functionality to specify an icon.
The Icons depend on font-awesome and hence require the yii2-extension `rmrevin/yii2-fontawesome`

###### AjaxToggleButton
An ajax button to toggle boolean values (1, 0). Together with `AjaxAttributeAction` this makes it
very easy to toggle boolean flags. The widget-attribute `booleanAttribute` is used only for reading out
values. Therefore you have to respecify this in the controller-action (step 2 below).

Example of usage together with `AjaxAttributeAction`:
```php
<?= AjaxToggleButton::widget([
	'model'=>$model,
	'booleanAttribute'=>'is_archived',
	'action'=>'toggle-archived',
	'options'=>['class'=>'btn-primary btn-xs'],
]);
```

Now attach an instance of `AjaxAttributeAction` in the corresponding controller of the model specified:
```php
public function actions()
{
	return [
		'toggle-archived'=>[
			'class'=>AjaxAttributeAction::className(),
			'targetClass'=>User::className(),
			'targetAttribute'=>'is_archived',
		],
	];
}
```


###### FlashMessages
This widget renders flash messages automatically. The messages can be automatically retrieved 
from yiis session-component or be provided via a custom callable.

Example of simple usage rendering yiis session flashes each in its own alert-container:
```php
<?= FlashMessages::widget() ?>
```

Advanced usage with custom callback to provide flash messages:
```php
<?= FlashMessages::widget([
    'loadFlashesCallback'=>function() {
        return ['info'=>'Hello', 'danger'=>'World!'];
    },
]) ?>
```

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

###### SimpleNav
Simple navigation widget which has the same functionality as the regular nav-widget (`\yii\bootstrap\Nav`) but 
renders a plain and simple HTML-list which can then be further styled with css. No dropdown...just clean code!

###### StatsBoxes
Renders a variable amount of stats boxes with title, icon and a value. This is ideal for a detail view of
a model.

###### TabsWithMemory
Tabs widget which remembers its active tab via javascript sessionStorage

###### Video
Wrapper-widget to simplify work with video tag

###### Grid column-types
The column types provided extend the functionality of the basic `\yii\grid\DataColumn`. The
class is `AdvancedDataColumn`. It has functionality to align text, set the column with with either
absolute or percent values etc.

Additionally there are three further column types:
* `AdvancedActionColumn` further functionality for the action column (rights per button, etc.)
* `AdvancedDataColumn` base class for regular data displaying with advanced functionality
* `BooleanColumn` optimized for rendering boolean values
* `IdColumn` optimized for rendering id values with or without code-tags
* `LinkColumn` renders links (which can be generated using a closure)


### Helpers

###### ColorHelper
Makes working with HEX- or RGB-colors easy! It can translate between the two formats,
lighten or darken colors as well as creating steps between two colors. You can also
use it to get a colors luminance or validate color-values.
Short HEX-Formats are supported automatically.

###### ComponentConfig
Helper class to work with component-configurations

###### DropdownHelper
Makes working with drop downs easier. Especially if you need additional options per item

###### Geocoding
Helper class to work with google geocoding api's. It enables you to do one-call forward and
reverse geocoding

###### Html
Extends the Html-helper of Yii2 with additional functionality like disguising email-addresses,
bootstrap-elements, text-highlighting, etc.

###### ImageFactory
Factory-class to create instances of image drivers. To use it, simply call `createInstance($path, $driver=self::DRIVER_GD)`
to get an instance and proceed working. Make sure either GD- or Imagick-Library is enabled.

###### MigrationHelper
Helper for common tasks concerning migrations (eg checking if a migration was applied, etc).

###### PrimaryKey
Functionalities to work with PKs of `\yii\db\ActiveRecord` and to convert them into JSON

###### QueryHelper
Recurring tasks while working wth ActiveQueries

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

###### StateBehavior / StateQueryBehavior
Documentation coming soon!


### Actions

###### AjaxAttributeAction
A generic and very convenient action to modify model-values via ajax-calls. See the class comments for how
to configure this action.

There is also an example further up in the description of `AjaxToggleButton`.

###### DebugAction
The debug action shows you relevant information about the current configuration of the hosting. It also
shows you all kind of configs right in the browser.

To enable it, all you have to to is add it to the `actions()`-method of a controller of your choice and
provide a view to render its contents into.

```php
public function actions()
{
    return [
        // ...
        [
            'class'=>'asinfotrack\yii2\toolbox\actions\DebugAction',
            'view'=>'//site/debugging',
        ]
        // ...
    ];
}
```

Within that view you simply output the contents with this statement:

```php
<?= $content ?>
```

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

### Console

###### ConsoleTarget
Log target which outputs to the console

###### Migration
Has additional functionality simplifying repeating tasks while creating migrations.

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
