<?php
namespace asinfotrack\yii2\toolbox\exceptions;

/**
 * A HTTP-exception representing the status-code 410 (gone) which is used to mark
 * a link as no longer valid.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license MIT
 */
class ExpiredHttpException extends \yii\web\HttpException
{

	/**
	 * Constructor.
	 *
	 * @param string $message error message
	 * @param integer $code error code
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = null, $code = 0, \Exception $previous = null)
	{
		parent::__construct(410, $message, $code, $previous);
	}

}
