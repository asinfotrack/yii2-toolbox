<?php
namespace asinfotrack\yii2\toolbox\components;

use Yii;
use yii\helpers\Html;

/**
 * The icon component is a central point of access for icon generation. It can
 * be used as is or be attached to the application as a component for a common
 * configuration.
 *
 * To use it you can simply call: `Icon::create('icon-name')`
 * This will either use the singleton application component (if defined) or a
 * one off instance.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class Icon extends \yii\base\Component
{

	/**
	 * @var string the component name under which the instance is configured in the
	 * yii config. If there is no instance under this name, a new one will be created
	 * for one time use only.
	 */
	public static $COMPONENT_NAME = 'icon';

	/**
	 * @var bool flag whether or not font awesome 4 extension by rmrevin is installed
	 */
	protected $hasExtFa4;

	/**
	 * @var bool flag whether or not font awesome 5 extension by rmrevin is installed
	 */
	protected $hasExtFa5;

	/**
	 * @var callable optional callable to create icons in a custom way. If
	 * implemented, the callback should have the signature `function ($iconName)`
	 * and return the html code of the icon.
	 */
	public $createIconCallback;

	/**
	 * @var array optional map to replace icon names with alternatives. Specify
	 * this property as an array in which the keys are the icon names to replace
	 * and the values are the names which will replace them.
	 */
	public $replaceMap = [];

	/**
	 * This is an alias method for `Icon::create()`
	 *
	 * @see \asinfotrack\yii2\toolbox\components\Icon::create()
	 *
	 * @param string $iconName the desired icon name
	 * @param array $options options array for the icon
	 * @return string the icon code
	 */
	public static function c($iconName, $options=[])
	{
		return static::create($iconName, $options);
	}

	/**
	 * Shorthand method to create an icon.
	 *
	 * The method will use the singleton component instance if defined under `Yii::$app->icon` or
	 * a one time instance if not defined.
	 *
	 * @param string $iconName the desired icon name
	 * @param array $options options array for the icon
	 * @return string the icon code
	 */
	public static function create($iconName, $options=[])
	{
		if (isset(Yii::$app->{static::$COMPONENT_NAME}) && Yii::$app->{static::$COMPONENT_NAME} instanceof Icon) {
			$instance = Yii::$app->{static::$COMPONENT_NAME};
		} else {
			$instance = new Icon();
		}

		return $instance->createIcon($iconName, $options);
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		//check presence of font awesome extensions
		$this->hasExtFa4 = class_exists('rmrevin\yii\fontawesome\FA');
		$this->hasExtFa5 = class_exists('rmrevin\yii\fontawesome\FAB');
	}

	/**
	 * Creates an icon with either a specified callback or by default in the following order:
	 *
	 * 1. FontAwesome 5 with rmrevin-extension in version 3.*
	 * 2. FontAwesome 4 with rmrevin-extension in version 2.*
	 * 3. yii default glyph icons
	 *
	 * @param string $iconName the desired icon name
	 * @param array $options options array for the icon
	 * @return string the icon code
	 */
	public function createIcon($iconName, $options=[])
	{
		//replace icon name if necessary
		$iconName = $this->replaceIconName($iconName);

		//create the actual icon
		if (is_callable($this->createIconCallback)) {
			return call_user_func($this->createIconCallback, $iconName, $options);
		} else {
			if ($this->hasExtFa5) {
				$fa5Class = 'FAS';
				if (preg_match('/^fa[srlb]-/', $iconName)) {
					$fa5Class = strtoupper(substr($iconName, 0, 3));
					$iconName = substr($iconName, 4);
				} else if (isset(static::$FA_SHIMS[$iconName])) {
					$shimData = static::$FA_SHIMS[$iconName];
					if ($shimData[0] !== null) $fa5Class = strtoupper($shimData[0]);
					if ($shimData[1] !== null) $iconName = $shimData[1];
				}

				return call_user_func(['rmrevin\yii\fontawesome\\' . $fa5Class, 'icon'], $iconName, $options);
			} else if ($this->hasExtFa4) {
				return call_user_func(['rmrevin\yii\fontawesome\FA', 'icon'], $iconName, $options);
			} else {
				Html::addCssClass($options, 'glyphicon');
				Html::addCssClass($options, 'glyphicon-' . $iconName);
				return Html::tag('span', '', $options);
			}
		}
	}

	/**
	 * Replaces an old icon name with an alternative provided in the replaceMap of the class
	 *
	 * @param string $iconName the icon name to replace if necessary
	 * @return string the final icon name
	 */
	protected function replaceIconName($iconName)
	{
		return isset($this->replaceMap[$iconName]) ? $this->replaceMap[$iconName] : $iconName;
	}

	/**
	 * @var array font awesome 5 shims as provided by the library
	 */
	protected static $FA_SHIMS = [
		'500px'=>['fab', null],
		'address-book-o'=>['far', 'address-book'],
		'address-card-o'=>['far', 'address-card'],
		'adn'=>['fab', null],
		'amazon'=>['fab', null],
		'android'=>['fab', null],
		'angellist'=>['fab', null],
		'apple'=>['fab', null],
		'area-chart'=>[null, 'chart-area'],
		'arrow-circle-o-down'=>['far', 'arrow-alt-circle-down'],
		'arrow-circle-o-left'=>['far', 'arrow-alt-circle-left'],
		'arrow-circle-o-right'=>['far', 'arrow-alt-circle-right'],
		'arrow-circle-o-up'=>['far', 'arrow-alt-circle-up'],
		'arrows'=>[null, 'arrows-alt'],
		'arrows-alt'=>[null, 'expand-arrows-alt'],
		'arrows-h'=>[null, 'arrows-alt-h'],
		'arrows-v'=>[null, 'arrows-alt-v'],
		'asl-interpreting'=>[null, 'american-sign-language-interpreting'],
		'automobile'=>[null, 'car'],
		'bandcamp'=>['fab', null],
		'bank'=>[null, 'university'],
		'bar-chart'=>['far', 'chart-bar'],
		'bar-chart-o'=>['far', 'chart-bar'],
		'bathtub'=>[null, 'bath'],
		'battery'=>[null, 'battery-full'],
		'battery-0'=>[null, 'battery-empty'],
		'battery-1'=>[null, 'battery-quarter'],
		'battery-2'=>[null, 'battery-half'],
		'battery-3'=>[null, 'battery-three-quarters'],
		'battery-4'=>[null, 'battery-full'],
		'behance'=>['fab', null],
		'behance-square'=>['fab', null],
		'bell-o'=>['far', 'bell'],
		'bell-slash-o'=>['far', 'bell-slash'],
		'bitbucket'=>['fab', null],
		'bitbucket-square'=>['fab', 'bitbucket'],
		'bitcoin'=>['fab', 'btc'],
		'black-tie'=>['fab', null],
		'bluetooth'=>['fab', null],
		'bluetooth-b'=>['fab', null],
		'bookmark-o'=>['far', 'bookmark'],
		'btc'=>['fab', null],
		'building-o'=>['far', 'building'],
		'buysellads'=>['fab', null],
		'cab'=>[null, 'taxi'],
		'calendar'=>[null, 'calendar-alt'],
		'calendar-check-o'=>['far', 'calendar-check'],
		'calendar-minus-o'=>['far', 'calendar-minus'],
		'calendar-o'=>['far', 'calendar'],
		'calendar-plus-o'=>['far', 'calendar-plus'],
		'calendar-times-o'=>['far', 'calendar-times'],
		'caret-square-o-down'=>['far', 'caret-square-down'],
		'caret-square-o-left'=>['far', 'caret-square-left'],
		'caret-square-o-right'=>['far', 'caret-square-right'],
		'caret-square-o-up'=>['far', 'caret-square-up'],
		'cc'=>['far', 'closed-captioning'],
		'cc-amex'=>['fab', null],
		'cc-diners-club'=>['fab', null],
		'cc-discover'=>['fab', null],
		'cc-jcb'=>['fab', null],
		'cc-mastercard'=>['fab', null],
		'cc-paypal'=>['fab', null],
		'cc-stripe'=>['fab', null],
		'cc-visa'=>['fab', null],
		'chain'=>[null, 'link'],
		'chain-broken'=>[null, 'unlink'],
		'check-circle-o'=>['far', 'check-circle'],
		'check-square-o'=>['far', 'check-square'],
		'chrome'=>['fab', null],
		'circle-o'=>['far', 'circle'],
		'circle-o-notch'=>[null, 'circle-notch'],
		'circle-thin'=>['far', 'circle'],
		'clipboard'=>['far', null],
		'clock-o'=>['far', 'clock'],
		'clone'=>['far', null],
		'close'=>[null, 'times'],
		'cloud-download'=>[null, 'cloud-download-alt'],
		'cloud-upload'=>[null, 'cloud-upload-alt'],
		'cny'=>[null, 'yen-sign'],
		'code-fork'=>[null, 'code-branch'],
		'codepen'=>['fab', null],
		'codiepie'=>['fab', null],
		'comment-o'=>['far', 'comment'],
		'commenting'=>[null, 'comment-alt'],
		'commenting-o'=>['far', 'comment-alt'],
		'comments-o'=>['far', 'comments'],
		'compass'=>['far', null],
		'connectdevelop'=>['fab', null],
		'contao'=>['fab', null],
		'copyright'=>['far', null],
		'creative-commons'=>['fab', null],
		'credit-card'=>['far', null],
		'credit-card-alt'=>[null, 'credit-card'],
		'css3'=>['fab', null],
		'cutlery'=>[null, 'utensils'],
		'dashboard'=>[null, 'tachometer-alt'],
		'dashcube'=>['fab', null],
		'deafness'=>[null, 'deaf'],
		'dedent'=>[null, 'outdent'],
		'delicious'=>['fab', null],
		'deviantart'=>['fab', null],
		'diamond'=>['far', 'gem'],
		'digg'=>['fab', null],
		'dollar'=>[null, 'dollar-sign'],
		'dot-circle-o'=>['far', 'dot-circle'],
		'dribbble'=>['fab', null],
		'drivers-license'=>[null, 'id-card'],
		'drivers-license-o'=>['far', 'id-card'],
		'dropbox'=>['fab', null],
		'drupal'=>['fab', null],
		'edge'=>['fab', null],
		'eercast'=>['fab', 'sellcast'],
		'empire'=>['fab', null],
		'envelope-o'=>['far', 'envelope'],
		'envelope-open-o'=>['far', 'envelope-open'],
		'envira'=>['fab', null],
		'etsy'=>['fab', null],
		'eur'=>[null, 'euro-sign'],
		'euro'=>[null, 'euro-sign'],
		'exchange'=>[null, 'exchange-alt'],
		'expeditedssl'=>['fab', null],
		'external-link'=>[null, 'external-link-alt'],
		'external-link-square'=>[null, 'external-link-square-alt'],
		'eye-slash'=>['far', null],
		'eyedropper'=>[null, 'eye-dropper'],
		'fa'=>['fab', 'font-awesome'],
		'facebook'=>['fab', 'facebook-f'],
		'facebook-f'=>['fab', 'facebook-f'],
		'facebook-official'=>['fab', 'facebook'],
		'facebook-square'=>['fab', null],
		'feed'=>[null, 'rss'],
		'file-archive-o'=>['far', 'file-archive'],
		'file-audio-o'=>['far', 'file-audio'],
		'file-code-o'=>['far', 'file-code'],
		'file-excel-o'=>['far', 'file-excel'],
		'file-image-o'=>['far', 'file-image'],
		'file-movie-o'=>['far', 'file-video'],
		'file-o'=>['far', 'file'],
		'file-pdf-o'=>['far', 'file-pdf'],
		'file-photo-o'=>['far', 'file-image'],
		'file-picture-o'=>['far', 'file-image'],
		'file-powerpoint-o'=>['far', 'file-powerpoint'],
		'file-sound-o'=>['far', 'file-audio'],
		'file-text'=>[null, 'file-alt'],
		'file-text-o'=>['far', 'file-alt'],
		'file-video-o'=>['far', 'file-video'],
		'file-word-o'=>['far', 'file-word'],
		'file-zip-o'=>['far', 'file-archive'],
		'files-o'=>['far', 'copy'],
		'firefox'=>['fab', null],
		'first-order'=>['fab', null],
		'flag-o'=>['far', 'flag'],
		'flash'=>[null, 'bolt'],
		'flickr'=>['fab', null],
		'floppy-o'=>['far', 'save'],
		'folder-o'=>['far', 'folder'],
		'folder-open-o'=>['far', 'folder-open'],
		'font-awesome'=>['fab', null],
		'fonticons'=>['fab', null],
		'fort-awesome'=>['fab', null],
		'forumbee'=>['fab', null],
		'foursquare'=>['fab', null],
		'free-code-camp'=>['fab', null],
		'frown-o'=>['far', 'frown'],
		'futbol-o'=>['far', 'futbol'],
		'gbp'=>[null, 'pound-sign'],
		'ge'=>['fab', 'empire'],
		'gear'=>[null, 'cog'],
		'gears'=>[null, 'cogs'],
		'get-pocket'=>['fab', null],
		'gg'=>['fab', null],
		'gg-circle'=>['fab', null],
		'git'=>['fab', null],
		'git-square'=>['fab', null],
		'github'=>['fab', null],
		'github-alt'=>['fab', null],
		'github-square'=>['fab', null],
		'gitlab'=>['fab', null],
		'gittip'=>['fab', 'gratipay'],
		'glass'=>[null, 'glass-martini'],
		'glide'=>['fab', null],
		'glide-g'=>['fab', null],
		'google'=>['fab', null],
		'google-plus'=>['fab', 'google-plus-g'],
		'google-plus-circle'=>['fab', 'google-plus'],
		'google-plus-official'=>['fab', 'google-plus'],
		'google-plus-square'=>['fab', null],
		'google-wallet'=>['fab', null],
		'gratipay'=>['fab', null],
		'grav'=>['fab', null],
		'group'=>[null, 'users'],
		'hacker-news'=>['fab', null],
		'hand-grab-o'=>['far', 'hand-rock'],
		'hand-lizard-o'=>['far', 'hand-lizard'],
		'hand-o-down'=>['far', 'hand-point-down'],
		'hand-o-left'=>['far', 'hand-point-left'],
		'hand-o-right'=>['far', 'hand-point-right'],
		'hand-o-up'=>['far', 'hand-point-up'],
		'hand-paper-o'=>['far', 'hand-paper'],
		'hand-peace-o'=>['far', 'hand-peace'],
		'hand-pointer-o'=>['far', 'hand-pointer'],
		'hand-rock-o'=>['far', 'hand-rock'],
		'hand-scissors-o'=>['far', 'hand-scissors'],
		'hand-spock-o'=>['far', 'hand-spock'],
		'hand-stop-o'=>['far', 'hand-paper'],
		'handshake-o'=>['far', 'handshake'],
		'hard-of-hearing'=>[null, 'deaf'],
		'hdd-o'=>['far', 'hdd'],
		'header'=>[null, 'heading'],
		'heart-o'=>['far', 'heart'],
		'hospital-o'=>['far', 'hospital'],
		'hotel'=>[null, 'bed'],
		'hourglass-1'=>[null, 'hourglass-start'],
		'hourglass-2'=>[null, 'hourglass-half'],
		'hourglass-3'=>[null, 'hourglass-end'],
		'hourglass-o'=>['far', 'hourglass'],
		'houzz'=>['fab', null],
		'html5'=>['fab', null],
		'id-badge'=>['far', null],
		'id-card-o'=>['far', 'id-card'],
		'ils'=>[null, 'shekel-sign'],
		'image'=>['far', 'image'],
		'imdb'=>['fab', null],
		'inr'=>[null, 'rupee-sign'],
		'instagram'=>['fab', null],
		'institution'=>[null, 'university'],
		'internet-explorer'=>['fab', null],
		'intersex'=>[null, 'transgender'],
		'ioxhost'=>['fab', null],
		'joomla'=>['fab', null],
		'jpy'=>[null, 'yen-sign'],
		'jsfiddle'=>['fab', null],
		'keyboard-o'=>['far', 'keyboard'],
		'krw'=>[null, 'won-sign'],
		'lastfm'=>['fab', null],
		'lastfm-square'=>['fab', null],
		'leanpub'=>['fab', null],
		'legal'=>[null, 'gavel'],
		'lemon-o'=>['far', 'lemon'],
		'level-down'=>[null, 'level-down-alt'],
		'level-up'=>[null, 'level-up-alt'],
		'life-bouy'=>['far', 'life-ring'],
		'life-buoy'=>['far', 'life-ring'],
		'life-ring'=>['far', null],
		'life-saver'=>['far', 'life-ring'],
		'lightbulb-o'=>['far', 'lightbulb'],
		'line-chart'=>[null, 'chart-line'],
		'linkedin'=>['fab', 'linkedin-in'],
		'linkedin-square'=>['fab', 'linkedin'],
		'linode'=>['fab', null],
		'linux'=>['fab', null],
		'list-alt'=>['far', null],
		'long-arrow-down'=>[null, 'long-arrow-alt-down'],
		'long-arrow-left'=>[null, 'long-arrow-alt-left'],
		'long-arrow-right'=>[null, 'long-arrow-alt-right'],
		'long-arrow-up'=>[null, 'long-arrow-alt-up'],
		'mail-forward'=>[null, 'share'],
		'mail-reply'=>[null, 'reply'],
		'mail-reply-all'=>[null, 'reply-all'],
		'map-marker'=>[null, 'map-marker-alt'],
		'map-o'=>['far', 'map'],
		'maxcdn'=>['fab', null],
		'meanpath'=>['fab', 'font-awesome'],
		'medium'=>['fab', null],
		'meetup'=>['fab', null],
		'meh-o'=>['far', 'meh'],
		'minus-square-o'=>['far', 'minus-square'],
		'mixcloud'=>['fab', null],
		'mobile'=>[null, 'mobile-alt'],
		'mobile-phone'=>[null, 'mobile-alt'],
		'modx'=>['fab', null],
		'money'=>['far', 'money-bill-alt'],
		'moon-o'=>['far', 'moon'],
		'mortar-board'=>[null, 'graduation-cap'],
		'navicon'=>[null, 'bars'],
		'newspaper-o'=>['far', 'newspaper'],
		'object-group'=>['far', null],
		'object-ungroup'=>['far', null],
		'odnoklassniki'=>['fab', null],
		'odnoklassniki-square'=>['fab', null],
		'opencart'=>['fab', null],
		'openid'=>['fab', null],
		'opera'=>['fab', null],
		'optin-monster'=>['fab', null],
		'pagelines'=>['fab', null],
		'paper-plane-o'=>['far', 'paper-plane'],
		'paste'=>['far', 'clipboard'],
		'pause-circle-o'=>['far', 'pause-circle'],
		'paypal'=>['fab', null],
		'pencil'=>[null, 'pencil-alt'],
		'pencil-square'=>[null, 'pen-square'],
		'pencil-square-o'=>['far', 'edit'],
		'photo'=>['far', 'image'],
		'picture-o'=>['far', 'image'],
		'pie-chart'=>[null, 'chart-pie'],
		'pied-piper'=>['fab', null],
		'pied-piper-alt'=>['fab', null],
		'pied-piper-pp'=>['fab', null],
		'pinterest'=>['fab', null],
		'pinterest-p'=>['fab', null],
		'pinterest-square'=>['fab', null],
		'play-circle-o'=>['far', 'play-circle'],
		'plus-square-o'=>['far', 'plus-square'],
		'product-hunt'=>['fab', null],
		'qq'=>['fab', null],
		'question-circle-o'=>['far', 'question-circle'],
		'quora'=>['fab', null],
		'ra'=>['fab', 'rebel'],
		'ravelry'=>['fab', null],
		'rebel'=>['fab', null],
		'reddit'=>['fab', null],
		'reddit-alien'=>['fab', null],
		'reddit-square'=>['fab', null],
		'refresh'=>[null, 'sync'],
		'registered'=>['far', null],
		'remove'=>[null, 'times'],
		'renren'=>['fab', null],
		'reorder'=>[null, 'bars'],
		'repeat'=>[null, 'redo'],
		'resistance'=>['fab', 'rebel'],
		'rmb'=>[null, 'yen-sign'],
		'rotate-left'=>[null, 'undo'],
		'rotate-right'=>[null, 'redo'],
		'rouble'=>[null, 'ruble-sign'],
		'rub'=>[null, 'ruble-sign'],
		'ruble'=>[null, 'ruble-sign'],
		'rupee'=>[null, 'rupee-sign'],
		's15'=>[null, 'bath'],
		'safari'=>['fab', null],
		'scissors'=>[null, 'cut'],
		'scribd'=>['fab', null],
		'sellsy'=>['fab', null],
		'send'=>[null, 'paper-plane'],
		'send-o'=>['far', 'paper-plane'],
		'share-square-o'=>['far', 'share-square'],
		'shekel'=>[null, 'shekel-sign'],
		'sheqel'=>[null, 'shekel-sign'],
		'shield'=>[null, 'shield-alt'],
		'shirtsinbulk'=>['fab', null],
		'sign-in'=>[null, 'sign-in-alt'],
		'sign-out'=>[null, 'sign-out-alt'],
		'signing'=>[null, 'sign-language'],
		'simplybuilt'=>['fab', null],
		'skyatlas'=>['fab', null],
		'skype'=>['fab', null],
		'slack'=>['fab', null],
		'sliders'=>[null, 'sliders-h'],
		'slideshare'=>['fab', null],
		'smile-o'=>['far', 'smile'],
		'snapchat'=>['fab', null],
		'snapchat-ghost'=>['fab', null],
		'snapchat-square'=>['fab', null],
		'snowflake-o'=>['far', 'snowflake'],
		'soccer-ball-o'=>['far', 'futbol'],
		'sort-alpha-asc'=>[null, 'sort-alpha-down'],
		'sort-alpha-desc'=>[null, 'sort-alpha-up'],
		'sort-amount-asc'=>[null, 'sort-amount-down'],
		'sort-amount-desc'=>[null, 'sort-amount-up'],
		'sort-asc'=>[null, 'sort-up'],
		'sort-desc'=>[null, 'sort-down'],
		'sort-numeric-asc'=>[null, 'sort-numeric-down'],
		'sort-numeric-desc'=>[null, 'sort-numeric-up'],
		'soundcloud'=>['fab', null],
		'spoon'=>[null, 'utensil-spoon'],
		'spotify'=>['fab', null],
		'square-o'=>['far', 'square'],
		'stack-exchange'=>['fab', null],
		'stack-overflow'=>['fab', null],
		'star-half-empty'=>['far', 'star-half'],
		'star-half-full'=>['far', 'star-half'],
		'star-half-o'=>['far', 'star-half'],
		'star-o'=>['far', 'star'],
		'steam'=>['fab', null],
		'steam-square'=>['fab', null],
		'sticky-note-o'=>['far', 'sticky-note'],
		'stop-circle-o'=>['far', 'stop-circle'],
		'stumbleupon'=>['fab', null],
		'stumbleupon-circle'=>['fab', null],
		'sun-o'=>['far', 'sun'],
		'superpowers'=>['fab', null],
		'support'=>['far', 'life-ring'],
		'tablet'=>[null, 'tablet-alt'],
		'tachometer'=>[null, 'tachometer-alt'],
		'telegram'=>['fab', null],
		'television'=>[null, 'tv'],
		'tencent-weibo'=>['fab', null],
		'themeisle'=>['fab', null],
		'thermometer'=>[null, 'thermometer-full'],
		'thermometer-0'=>[null, 'thermometer-empty'],
		'thermometer-1'=>[null, 'thermometer-quarter'],
		'thermometer-2'=>[null, 'thermometer-half'],
		'thermometer-3'=>[null, 'thermometer-three-quarters'],
		'thermometer-4'=>[null, 'thermometer-full'],
		'thumb-tack'=>[null, 'thumbtack'],
		'thumbs-o-down'=>['far', 'thumbs-down'],
		'thumbs-o-up'=>['far', 'thumbs-up'],
		'ticket'=>[null, 'ticket-alt'],
		'times-circle-o'=>['far', 'times-circle'],
		'times-rectangle'=>[null, 'window-close'],
		'times-rectangle-o'=>['far', 'window-close'],
		'toggle-down'=>['far', 'caret-square-down'],
		'toggle-left'=>['far', 'caret-square-left'],
		'toggle-right'=>['far', 'caret-square-right'],
		'toggle-up'=>['far', 'caret-square-up'],
		'trash'=>[null, 'trash-alt'],
		'trash-o'=>['far', 'trash-alt'],
		'trello'=>['fab', null],
		'tripadvisor'=>['fab', null],
		'try'=>[null, 'lira-sign'],
		'tumblr'=>['fab', null],
		'tumblr-square'=>['fab', null],
		'turkish-lira'=>[null, 'lira-sign'],
		'twitch'=>['fab', null],
		'twitter'=>['fab', null],
		'twitter-square'=>['fab', null],
		'unsorted'=>[null, 'sort'],
		'usb'=>['fab', null],
		'usd'=>[null, 'dollar-sign'],
		'user-circle-o'=>['far', 'user-circle'],
		'user-o'=>['far', 'user'],
		'vcard'=>[null, 'address-card'],
		'vcard-o'=>['far', 'address-card'],
		'viacoin'=>['fab', null],
		'viadeo'=>['fab', null],
		'viadeo-square'=>['fab', null],
		'video-camera'=>[null, 'video'],
		'vimeo'=>['fab', 'vimeo-v'],
		'vimeo-square'=>['fab', null],
		'vine'=>['fab', null],
		'vk'=>['fab', null],
		'volume-control-phone'=>[null, 'phone-volume'],
		'warning'=>[null, 'exclamation-triangle'],
		'wechat'=>['fab', 'weixin'],
		'weibo'=>['fab', null],
		'weixin'=>['fab', null],
		'whatsapp'=>['fab', null],
		'wheelchair-alt'=>['fab', 'accessible-icon'],
		'wikipedia-w'=>['fab', null],
		'window-close-o'=>['far', 'window-close'],
		'window-maximize'=>['far', null],
	];

}
