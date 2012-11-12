<?
namespace RM\AssetsCollector\Compilers;

use \Nette\Object,
	\RM\AssetsCollector;

/**
 * Base class for CSS/JS file compilers.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
abstract class BaseAssetsCompiler extends Object
{
	/** @var string content of processed file */
	protected $input;

	/** @var string input after compile */
	protected $output;

	/** @var string base path for css files */
	public $cssPath;

	/** @var string base path for css files */
	public $jsPath;

	/** @var string */
	public $wwwDir;

	/** @var webTemp folder */
	public $webTemp;

	/**
	 * Get smaller variable from input/output
	 * @param	input string
	 * @return	output string
	 */
	public function getSmaller()
	{
		return (strlen($this->output)<strlen($this->input))?$this->output:$this->input;
	}
}
