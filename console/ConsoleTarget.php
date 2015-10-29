<?php
namespace asinfotrack\yii2\toolbox\console;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Console;
use yii\log\Logger;

/**
 * Log target to write messages to standard output
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ConsoleTarget extends \yii\log\Target
{

	/**
	 * @var \Closure optional closure to format a line for output. The function
	 * needs to have the following signature: 'function ($text, $level, $category, $timestamp)'
	 */
	public $formatLineCallback;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//assert proper config of formatLineCallback
		if ($this->formatLineCallback !== null && !($this->formatLineCallback instanceof \Closure)) {
			$msg = Yii::t('app', 'formatLineCallback needs to be a closure');
			throw new InvalidConfigException($msg);
		}
	}


	/**
	 * @inheritdoc
	 */
	public function export()
	{
		//iterate over messages
		foreach ($this->messages as $message) {
			//fill vars
			list($text, $level, $category, $timestamp) = $message;

			//format line
			if ($this->formatLineCallback != null) {
				$line = call_user_func($this->formatLineCallback, $text, $level, $category, $timestamp);
			} else {
				$line = $this->formatLine($text, $level, $category, $timestamp);
			}

			//output
			Console::output($line);
		}
	}

	protected function formatLine($text, $level, $category, $timestamp)
	{
		return sprintf(
			"%-19s [%-7s] [%s]\n                    %s",
			Yii::$app->formatter->asDatetime($timestamp, 'php:d.m.Y H:i:s'),
			strtoupper(Logger::getLevelName($level)),
			$category,
			$text
		);
	}

}
