<?
namespace RM\AssetsCollector\Compilers;

/**
 * CSS minificator.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class CssSimpleMinificator extends BaseCssAssetsCompiler implements IAssetsCompiler
{
	/**
	 * Get compiled content.
	 * @param	input string
	 * @return	output string
	 */
	public function compile($input,$dir=null)
	{
		$this->input = $this->output = $input;

		/* Strips Comments */
		$this->output = preg_replace('!/\*.*?\*/!s','', $this->output);
		$this->output = preg_replace('/\n\s*\n/',"\n", $this->output);

		/* Minifies */
		$this->output = preg_replace('/[\n\r \t]/',' ', $this->output);
		$this->output = preg_replace('/ +/',' ', $this->output);
		$this->output = preg_replace('/ ?([,:;{}]) ?/','$1',$this->output);

		/* Kill Trailing Semicolon, Contributed by Oliver */
		$this->output = preg_replace('/;}/','}',$this->output);

		return $this->getSmaller();
	}
}
