<?php
namespace asinfotrack\yii2\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Asset to register the js-toolbox script.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class JsToolboxAsset extends AssetBundle
{

	public $sourcePath = '@vendor/asinfotrack/yii2-toolbox/assets/src';

	public $js = [
		'toolbox.js',
	];

}
