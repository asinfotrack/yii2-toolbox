# yii2-toolbox
Yii2-Toolbox is a collection of useful helpers, widgets etc. extending the basic functionality of Yii2


## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

	"asinfotrack/yii2-toolbox": "*"


## Contents

### Widgets

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

### Helpers

###### Html
Extends the Html-helper of Yii2 with additional functionality

###### Timestamp
This helper is responsible for common tasks associated with UNIX-Timestamps
