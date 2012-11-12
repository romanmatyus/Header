<?
namespace RM\AssetsCollector\Compilers;

use \Nette\Object;

/**
 * Base class for CSS file compilers.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
abstract class BaseCssAssetsCompiler extends BaseAssetsCompiler
{
	/**
	 * Get all images from CSS content.
	 * @return array
	 */
	public function getImages()
	{
		preg_match_all('~\bbackground(-image)?\s*:(.*?)url\s*\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $this->input, $matches);
		$images = array();
		foreach ($matches['image'] as $image)
			if (!(substr($image,0,5)==="data:") && !(strpos($image,"base64")))
				$images[] = $image;
		return array_unique($images);
	}
}
