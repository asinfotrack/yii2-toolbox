<?php
namespace asinfotrack\yii2\toolbox\components;

/**
 * Additional formatter to configure Responses to show PDF-files either
 * in-browser or via force-download.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class PdfResponseFormatter extends \yii\base\Component implements \yii\web\ResponseFormatterInterface
{

	/**
	 * @var boolean if set to true, the file will be forced to download. Otherwise
	 * the pdf will be displayed inline.
	 */
	public $forceDownload = false;

	/**
	 * @var string the content type to use for the response (defaults to 'application/pdf')
	 */
	public $pdfContentType = 'application/pdf';

	/**
	 * @inheritdoc
	 */
	public function format($response)
	{
		/* @var $response \yii\web\Response */

		$response->getHeaders()->set('Content-Type', $this->pdfContentType);
		$response->content = $response->data;
		$response->setDownloadHeaders(null, null, !$this->forceDownload);
	}

}
