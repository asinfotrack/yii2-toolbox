<?php
namespace asinfotrack\yii2\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Asset to enable ajax toggle button functionality
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class AjaxToggleButtonAsset extends AssetBundle
{

	public $sourcePath = '@vendor/asinfotrack/yii2-toolbox/assets/src';

	public $js = [
		'ajax-toggle-button.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

}
