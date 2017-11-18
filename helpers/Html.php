<?php
namespace asinfotrack\yii2\toolbox\helpers;

use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url as UrlOriginal;
use asinfotrack\yii2\toolbox\assets\EmailDisguiseAsset;

/**
 * This helper extends the basic functionality of the Yii2-Html-helper.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Html extends \yii\helpers\Html
{

	const BS_DEFAULT 	= 'default';
	const BS_PRIMARY 	= 'primary';
	const BS_SUCCESS	= 'success';
	const BS_INFO		= 'info';
	const BS_WARNING	= 'warning';
	const BS_DANGER		= 'danger';

	const BS_XS			= 'xs';
	const BS_SM			= 'sm';
	const BS_MD			= 'md';
	const BS_LG			= 'lg';

	/**
	 * Creates a css-class to identify the html-tag by the current controller-,
	 * action- and module-ids.
	 *
	 * On in the index-action of the SiteController this method would return
	 * site-index. In module cms, within the article controllers index-action
	 * it would return cms-article-index.
	 *
	 * @param string $glue the glue to join the parts (defaults to '-')
	 * @return string the css-class
	 */
	public static function htmlClass($glue='-')
	{
		$parts = [
			$parts[] = Yii::$app->controller->id,
			$parts[] = Yii::$app->controller->action->id,
		];

		if (Yii::$app->module !== null) {
			array_unshift($parts, Yii::$app->module->id);
		}

		return implode($glue, $parts);
	}

	/**
	 * Renders a quantity with its corresponding unit (eg '107.00 kg')
	 *
	 * @param float $value the quantity
	 * @param string $unit the unit descriptor string
	 * @param integer $decimals number of decimals
	 * @param boolean $muted whether or not to mute the unit symbol
	 * @return string generated html-code
	 */
	public static function valueWithUnit($value, $unit, $decimals=2, $muted=true)
	{
		return Yii::$app->formatter->asDecimal($value, $decimals)
			   . ' '
			   . $muted ? static::tag('span', $unit, ['class'=>'text-muted']) : $unit;
	}

	/**
	 * Creates a bootstrap-badge
	 *
	 * @param string $content the content of the badge
	 * @param boolean $encode whether or not to encode the content (defaults to true)
	 * @return string generated html-code
	 */
	public static function bsBadge($content, $encode=true)
	{
		return static::tag('span', $encode ? static::encode($content) : $content, ['class'=>'badge']);
	}

	/**
	 * Creates a bootstrap label
	 *
	 * @param string $content the content of the label
	 * @param string $type the bootstrap type (eg alert, danger, etc.) which can be
	 * set via constants of this class
	 * @param boolean $encode whether or not to encode the content (defaults to true)
	 * @return string generated html-code
	 */
	public static function bsLabel($content, $type=self::BS_DEFAULT, $encode=true)
	{
		return static::tag('span', $encode ? static::encode($content) : $content, [
			'class'=>'label label-' . $type,
		]);
	}

	/**
	 * Creates a bootstrap list group.
	 * Each item can be specified as a simple string or an array. Subsequent are
	 * all valid indices of which \'content\' is mandatory.
	 *
	 * - content:	string				contains the content of the item (mandatory!)
	 * - options:	mixed[]				individual item options beeing merged with default item options
	 * - url:		string|array		either a raw url in string format or an array as needed by
	 * 									yiis url-helper (@see \yii\helpers\Url::to())
	 * - type:		string				the bootstrap type (eg alert, danger, etc.) which can be
	 * 									set via constants of this class
	 * - active		boolean|\Closure	either a boolean value or an anonymous function returning
	 * 									a boolean value
	 * - badgeVal	string				content of an optional badge rendered inside the item
	 *
	 * @param string[]|mixed[] $items collection of items (each can be string or array)
	 * @param mixed[] $listOptions options for the list tag
	 * @param mixed[] $defaultItemOptions default options for list items (can be overriden in
	 * items individual options)
	 * @param string $listTagName tag name for the list (defaults to <code>ul</code>)
	 * @throws InvalidParamException if an item is specified in array-form and index \'content\'
	 * is not set
	 * @return string the code of the list group
	 */
	public function bsListGroup($items, $listOptions=[], $defaultItemOptions=[], $listTagName='ul')
	{
		//prepare vars
		static::addCssClass($defaultItemOptions, 'list-group-item');
		$ret = '';

		//iterate over items
		foreach ($items as $i) {
			if (is_array($i)) {
				//validate content
				if (!isset($i['content'])) {
					throw new InvalidParamException('the index \'content\' is mandatory for each list element');
				}
				//prepare options
				$itemOptions = $defaultItemOptions;
				if (isset($i['options'])) {
					$itemOptions = ArrayHelper::merge($itemOptions, $i['options']);
					static::addCssClass($itemOptions, 'list-group-item');
				}
				//prepare url if necessary
				$isLink = isset($i['url']);
				if ($isLink) {
					$itemOptions['href'] = is_array($i['url']) ? UrlOriginal::to($i['url']) : $i['url'];
				}
				//bs type
				if (isset($i['type'])) {
					static::addCssClass($itemOptions, 'list-group-item-' . $i['type']);
				}
				//active
				if (isset($i['active'])) {
					$isActive = $i['active'] instanceof \Closure ? call_user_func($i['active']) : $i['active'];
					if ($isActive) static::addCssClass($itemOptions, 'active');
				}
				//badge
				if (isset($i['badgeVal'])) $i['content'] = static::bsBadge($i['badgeVal']) . $i['content'];
				//render item
				$ret .= static::tag($isLink ? 'a' : 'li', $i['content'], $itemOptions) . "\n";
			} else {
				$ret .= static::tag('li', $i, $defaultItemOptions);
			}
		}

		//enclose and return
		static::addCssClass($listOptions, 'list-group');
		return static::tag($listTagName, $ret, $listOptions);
	}

	/**
	 * This method wraps a search term in a definable tag (defaults to strong) and enables
	 * highlighting of parts of a string. This is especially useful for highlighting search-
	 * terms in grid-view.
	 *
	 * @param string $haystack the string containing the whole text
	 * @param string $term the needle to highlight
	 * @param string $tagName name of the tag the highlighted parts will be wrapped in
	 * @param array $tagOptions options for the highlight-tag
	 * @return string the highlighted string
	 */
	public static function highlightTerm($haystack, $term=null, $tagName='strong', $tagOptions=[])
	{
		//check if nothing to highlight
		if ($term === null) return $haystack;

		//set vars
		$pos = 0;
		$startTag = Html::beginTag($tagName, $tagOptions);
		$endTag = Html::endTag($tagName);
		$length = strlen($term);

		//highlight the term-occurrences
		$pos = stripos($haystack, $term, $pos);
		while ($pos !== false) {
			$haystack = substr($haystack, 0, $pos)
					  . $startTag
					  . substr($haystack, $pos, $length)
					  . $endTag
					  . substr($haystack, $pos + $length);

			$pos = stripos($haystack, $term, $pos + strlen($startTag) + strlen($endTag));
		}

		return $haystack;
	}

	/**
	 * Generates a mailto hyperlink and disguises the email-address. The address is translated when
	 * link gets clicked.
	 *
	 * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code
	 * such as an image tag. If this is coming from end users, you should consider [[encode()]]
	 * it to prevent XSS attacks.
	 * @param string $email email address. If this is null, the first parameter (link body) will be treated
	 * as the email address and used.
	 * @param array $options the tag options in terms of name-value pairs. These will be rendered as
	 * the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
	 * If a value is null, the corresponding attribute will not be rendered.
	 * See [[renderTagAttributes()]] for details on how attributes are being rendered.
	 * @return string the generated mailto link
	 */
	public static function mailtoDisguised($text, $email=null, $options=[])
	{
		//register asset
		EmailDisguiseAsset::register(Yii::$app->getView());

		//get email-address and disguise everything
		if ($email === null) {
			$hiddenTag = Html::tag('span', Yii::$app->security->generateRandomString(8), ['style'=>'display: none']);

			$address = $text;
			$clear = str_replace('@', $hiddenTag . Html::tag('span', '@') . $hiddenTag, $address);
			$clear = str_replace('.', $hiddenTag . Html::tag('span', '.') . $hiddenTag, $clear);
		} else {
			$address = $email;
			$clear = $text;
		}
		$href = 'mailto:' . strrev(str_replace('@', '[at]', $address));

		//prepare options
		$options['href'] = $href;
		static::addCssClass($options, 'email-disguised');

		//return tag
		return static::tag('a', $clear, $options);
	}

}
