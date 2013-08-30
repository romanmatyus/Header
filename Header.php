<?php
namespace RM;

use Nette\FileNotFoundException,
	Nette\InvalidStateException,
	Nette\Utils\Html,
	Nette\Environment,
	Nette\Application\UI\Control;

/**
 * Header
 * This renderable component is ultimate solution for valid and complete HTML headers.
 *
 * @author Ondřej Mirtes
 * @copyright (c) Ondřej Mirtes 2009, 2010
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 * @package Header
 */
class Header extends Control
{

	/**
	 * doctypes
	 */
	const HTML_4 = self::HTML_4_STRICT; //backwards compatibility
	const HTML_4_STRICT = 'html4_strict';
	const HTML_4_TRANSITIONAL = 'html4_transitional';
	const HTML_4_FRAMESET = 'html4_frameset';

	const HTML_5 = 'html5';

	const XHTML_1 = self::XHTML_1_STRICT; //backwards compatibility
	const XHTML_1_STRICT = 'xhtml1_strict';
	const XHTML_1_TRANSITIONAL = 'xhtml1_transitional';
	const XHTML_1_FRAMESET = 'xhtml1_frameset';

	/**
	 * languages
	 */
	const CZECH = 'cs';
	const SLOVAK = 'sk';
	const ENGLISH = 'en';
	const GERMAN = 'de';

	/**
	 * content types
	 */
	const TEXT_HTML = 'text/html';
	const APPLICATION_XHTML = 'application/xhtml+xml';

	/** @var string doctype */
	private $docType;

	/** @var bool whether doctype is XML compatible or not */
	private $xml;

	/** @var string document language */
	private $language;

	/** @var string document title */
	private $title;

	/** @var string title separator */
	private $titleSeparator;

	/** @var bool whether title should be rendered in reverse order or not */
	private $titlesReverseOrder = TRUE;

	/** @var array document hierarchical titles */
	private $titles = array();

	/** @var array site rss channels */
	private $rssChannels = array();

	/** @var array header meta tags */
	private $metaTags = array();

	/** @var Html &lt;html&gt; tag */
	private $htmlTag;

	/** @var string document content type */
	private $contentType;

	/** @var bool whether XML content type should be forced or not */
	private $forceContentType;

	/** @var string path to favicon (without $basePath) */
	private $favicon;

	/** @var \RM\AssetsCollector */
	private $assetsCollector;

	public function __construct(IComponentContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->setDocType(self::HTML_5);
		$this->setContentType(self::TEXT_HTML);

		try {
			$this->setFavicon('/favicon.ico');
		} catch (FileNotFoundException $e) {

		}
	}

	public function setDocType($docType)
	{
		if ($docType == self::HTML_4_STRICT || $docType == self::HTML_4_TRANSITIONAL ||
				$docType == self::HTML_4_FRAMESET || $docType == self::HTML_5 ||
				$docType == self::XHTML_1_STRICT || $docType == self::XHTML_1_TRANSITIONAL ||
				$docType == self::XHTML_1_FRAMESET) {
			$this->docType = $docType;
			$this->xml = Html::$xhtml = ($docType == self::XHTML_1_STRICT ||
							$docType == self::XHTML_1_TRANSITIONAL ||
							$docType == self::XHTML_1_FRAMESET);
		} else {
			throw new InvalidArgumentException("Doctype $docType is not supported.");
		}

		return $this; //fluent interface
	}

	public function getDocType()
	{
		return $this->docType;
	}

	public function isXml()
	{
		return $this->xml;
	}

	public function setLanguage($language)
	{
		$this->language = $language;

		return $this; //fluent interface
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function setTitle($title)
	{
		if ($title != NULL && $title != '') {
			$this->title = $title;
		} else {
			throw new InvalidArgumentException("Title must be non-empty string.");
		}

		return $this; //fluent interface
	}

	public function getTitle($index = 0)
	{
		if (count($this->titles) == 0) {
			return $this->title;
		} else if (count($this->titles)-1-$index < 0) {
			return $this->getTitle();
		} else {
			return $this->titles[count($this->titles)-1-$index];
		}
	}

	public function addTitle($title)
	{
		if ($this->titleSeparator) {
			$this->titles[] = $title;
		} else {
			throw new InvalidStateException('Title separator is not set.');
		}

		return $this;
	}

	public function getTitles()
	{
		return $this->titles;
	}

	public function setTitleSeparator($separator)
	{
		$this->titleSeparator = $separator;

		return $this; //fluent interface
	}

	public function getTitleSeparator()
	{
		return $this->titleSeparator;
	}

	public function setTitlesReverseOrder($reverseOrder)
	{
		$this->titlesReverseOrder = (bool) $reverseOrder;

		return $this; //fluent interface
	}

	public function isTitlesOrderReversed()
	{
		return $this->titlesReverseOrder;
	}

	public function getTitleString()
	{
		if ($this->titles) {
			if (!$this->titlesReverseOrder) {
				array_unshift($this->titles, $this->title);
			} else {
				$this->titles = array_reverse($this->titles);
				ksort($this->titles);
				array_push($this->titles, $this->title);
			}

			return implode($this->titleSeparator, $this->titles);

		} else {
			return $this->title;
		}
	}

	public function addRssChannel($title, $link)
	{
		$this->rssChannels[] = array(
				'title' => $title,
				'link' => $link,
		);

		return $this; //fluent interface
	}

	public function getRssChannels()
	{
		return $this->rssChannels;
	}

	public function setContentType($contentType, $force = FALSE)
	{
		if ($contentType == self::APPLICATION_XHTML &&
				$this->docType != self::XHTML_1_STRICT && $this->docType != self::XHTML_1_TRANSITIONAL &&
				$this->docType != self::XHTML_1_FRAMESET) {
			throw new InvalidArgumentException("Cannot send $contentType type with non-XML doctype.");
		}

		if ($contentType == self::TEXT_HTML || $contentType == self::APPLICATION_XHTML) {
			$this->contentType = $contentType;
		} else {
			throw new InvalidArgumentException("Content type $contentType is not supported.");
		}

		$this->forceContentType = (bool) $force;

		return $this; //fluent interface
	}

	public function getContentType()
	{
		return $this->contentType;
	}

	public function isContentTypeForced()
	{
		return $this->forceContentType;
	}

	public function setFavicon($filename)
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $filename)) {
			$this->favicon = $filename;
		} else {
			throw new FileNotFoundException('Favicon ' . $_SERVER['DOCUMENT_ROOT'] . $filename . ' not found.');
		}

		return $this; //fluent interface
	}

	public function getFavicon()
	{
		return $this->favicon;
	}

	public function setMetaTag($name, $value)
	{
		$this->metaTags[$name] = $value;

		return $this; //fluent interface
	}

	public function getMetaTag($name)
	{
		return isset($this->metaTags[$name]) ? $this->metaTags[$name] : NULL;
	}

	public function getMetaTags()
	{
		return $this->metaTags;
	}

	public function setAuthor($author)
	{
		$this->setMetaTag('author', $author);

		return $this; //fluent interface
	}

	public function getAuthor()
	{
		return $this->getMetaTag('author');
	}

	public function setDescription($description)
	{
		$this->setMetaTag('description', $description);

		return $this; //fluent interface
	}

	public function getDescription()
	{
		return $this->getMetaTag('description');
	}

	public function addKeywords($keywords)
	{
		if (is_array($keywords)) {
			if ($this->keywords) {
				$this->setMetaTag('keywords', $this->getKeywords() . ', ' . implode(', ', $keywords));
			} else {
				$this->setMetaTag('keywords', implode(', ', $keywords));
			}
		} else if (is_string($keywords)) {
			if ($this->keywords) {
				$this->setMetaTag('keywords', $this->getKeywords() . ', ' . $keywords);
			} else {
				$this->setMetaTag('keywords', $keywords);
			}
		} else {
			throw new InvalidArgumentException('Type of keywords argument is not supported.');
		}

		return $this; //fluent interface
	}

	public function getKeywords()
	{
		return $this->getMetaTag('keywords');
	}

	public function setRobots($robots)
	{
		$this->setMetaTag('robots', $robots);

		return $this; //fluent interface
	}

	public function getRobots()
	{
		return $this->getMetaTag('robots');
	}

	public function setHtmlTag(Html $htmlTag)
	{
		$this->htmlTag = $html;

		return $this; // fluent interface
	}

	public function getHtmlTag()
	{
		if ($this->htmlTag == NULL) {
			$html = Html::el('html');

			if ($this->xml) {
				$html->attrs['xmlns'] = 'http://www.w3.org/1999/xhtml';
				$html->attrs['xml:lang'] = $this->language;
				$html->attrs['lang'] = $this->language;
			}

			if ($this->docType == self::HTML_5) {
				$html->attrs['lang'] = $this->language;
			}

			$this->htmlTag = $html;
		}

		return $this->htmlTag;
	}

	public function render()
	{
		$this->renderBegin();
		$this->renderRss();
		$this->renderCss();
		echo "\n";
		$this->renderJs();
		echo "\n";
		$this->renderEnd();
	}

	public function renderCss()
	{
		foreach ($this->assetsCollector->getCss() as $item) {
			$link = Html::el('link');
			$link->attrs['rel'] = 'stylesheet';
			$link->attrs['href'] = $item;
			echo $link . "\n";
		}
	}

	public function renderJs()
	{
		foreach ($this->assetsCollector->getJs() as $item) {
			$link = Html::el('script');
			$link->attrs['src'] = $item;
			echo $link . "\n";
		}
	}

	public function renderBegin()
	{
		$response = Environment::getHttpResponse();
		if ($this->docType == self::XHTML_1_STRICT &&
				$this->contentType == self::APPLICATION_XHTML &&
				($this->forceContentType || $this->isClientXhtmlCompatible())) {
			$contentType = self::APPLICATION_XHTML;
			if (!headers_sent()) {
				$response->setHeader('Vary', 'Accept');
			}
		} else {
			$contentType = self::TEXT_HTML;
		}

		if (!headers_sent()) {
			$response->setContentType($contentType, 'utf-8');
		}

		if ($contentType == self::APPLICATION_XHTML) {
			echo "<?xml version='1.0' encoding='utf-8'?>\n";
		}

		echo $this->getDocTypeString() . "\n";

		echo $this->getHtmlTag()->startTag() . "\n";

		echo Html::el('head')->startTag() . "\n";

		if ($this->docType != self::HTML_5) {
			$metaLanguage = Html::el('meta');
			$metaLanguage->attrs['http-equiv'] = 'Content-Language';
			$metaLanguage->content($this->language);
			echo $metaLanguage . "\n";
		}

		$metaContentType = Html::el('meta');
		$metaContentType->attrs['http-equiv'] = 'Content-Type';
		$metaContentType->content($contentType . '; charset=utf-8');
		echo $metaContentType . "\n";

		echo Html::el('title', $this->getTitleString()) . "\n";

		if ($this->favicon != '') {
			echo Html::el('link')->rel('shortcut icon')
					->href($this->favicon) . "\n";
		}

		foreach ($this->metaTags as $name=>$content) {
			echo Html::el('meta')->name($name)->content($content) . "\n";
		}
	}

	public function renderEnd()
	{
		echo Html::el('head')->endTag();
	}

	public function renderRss($channels = NULL)
	{
		if ($channels !== NULL) {
			$this->rssChannels = array();

			foreach ($channels as $title => $link) {
				$this->addRssChannel($title, $link);
			}
		}

		foreach ($this->rssChannels as $channel) {
			echo Html::el('link')->rel('alternate')->type('application/rss+xml')
					->title($channel['title'])
					->href(Environment::getApplication()->getPresenter()->link($channel['link'])) . "\n";
		}
	}

	private function getDocTypeString($docType = NULL)
	{
		if ($docType == NULL) {
			$docType = $this->docType;
		}

		switch ($docType) {
			case self::HTML_4_STRICT:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
			break;
			case self::HTML_4_TRANSITIONAL:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
			break;
			case self::HTML_4_FRAMESET:
				return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
			break;
			case self::HTML_5:
				return '<!DOCTYPE html>';
			break;
			case self::XHTML_1_STRICT:
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			break;
			case self::XHTML_1_TRANSITIONAL:
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			break;
			case self::XHTML_1_FRAMESET:
				return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
			break;
			default:
				throw new InvalidStateException("Doctype $docType is not supported.");
		}
	}

	private function isClientXhtmlCompatible()
	{
		$req = Environment::getHttpRequest();
		return stristr($req->getHeader('Accept'), 'application/xhtml+xml') ||
				$req->getHeader('Accept') == '*/*';
	}

	/**
	 * Add CSS files to header.
	 * Using \RM\AssetsCollector
	 * @param file string|array
	 * @return this
	 */
	public function addCss($file)
	{
		$this->assetsCollector->addCss($file);
		return $this;
	}

	/**
	 * Add JS files to header.
	 * Using \RM\AssetsCollector
	 * @param file string|array
	 * @return this
	 */
	public function addJs($file)
	{
		$this->assetsCollector->addJs($file);
		return $this;
	}

	/**
	 * Add CSS files to header from plain entry.
	 * Using \RM\AssetsCollector
	 * @param content string
	 * @return this
	 */
	public function addCssContent($content)
	{
		$this->assetsCollector->addCssContent($content);
		return $this;
	}

	/**
	 * Add JS files to header from plain entry.
	 * Using \RM\AssetsCollector
	 * @param content string
	 * @return this
	 */
	public function addJsContent($content)
	{
		$this->assetsCollector->addJsContent($content);
		return $this;
	}

	public function setAssetsCollector(AssetsCollector $assetsCollector)
	{
		if ($this->assetsCollector) {
			throw new \Nette\InvalidStateException('AssetsCollector has already been set');
		}
		$this->assetsCollector = $assetsCollector;
	}

	public function injectAssetsCollector(AssetsCollector $assetsCollector)
	{
		if ($this->assetsCollector) {
			throw new \Nette\InvalidStateException('AssetsCollector has already been set');
		}
		$this->assetsCollector = $assetsCollector;
	}
}
