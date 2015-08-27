<?php
namespace asinfotrack\yii2\toolbox\assets;

use yii\web\AssetBundle;

/**
 * Asset to enable email-disguising functionality used in extended html-helper
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class EmailDisguiseAsset extends AssetBundle
{

	public $sourcePath = '@vendor/asinfotrack/yii2-toolbox/assets/src';

	public $js = [
		'email-disguise.js',
	];

	public $depends = [
		'yii\web\JqueryAsset',
	];

}
