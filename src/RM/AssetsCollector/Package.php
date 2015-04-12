<?php
namespace RM\AssetsCollector;

use Nette\Object;

/**
 * Class of Packages for AssetsCollector.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class Package extends Object
{
	/** @var string name of package */
	private $name;

	/** @var array list of packages where this package extends */
	public $extends;

	/** @var array list includet css files */
	public $css;

	/** @var array list includet js files */
	public $js;

	/**
	 * Define package.
	 * @param string     $name    Name of package
	 * @param array|null $extends Array of packages where this package extends
	 * @param array|null $css     Array of included CSS files
	 * @param array|null $js      Array of included JS files
	 */
	public function __construct($name, array $extends = null, array $css = null, array $js = null)
	{
		$this->name = $name;
		$this->extends = $extends;
		$this->css = $css;
		$this->js = $js;
	}
}
