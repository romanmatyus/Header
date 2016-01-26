<?php

namespace RM\AssetsCollector\Compilers;

use Nette\Templating\Helpers;
use RM\AssetsCollector\AssetsCollector;

/**
 * CSS compiler where replace images in content to data stream.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class ImageToDataStream extends BaseCssAssetsCompiler implements IAssetsCompiler
{
	/** @var int maximal size of processed image */
	public $maxSize;

	/**
	 * Get compiled content
	 * @param  string $input
	 * @param  string $dir
	 * @return string
	 */
	public function compile($input, $dir = NULL)
	{
		$this->input = $this->output = $input;
		$images = $this->getImages();
		if ($images) {
			foreach($images as $img) {
					$source_file = AssetsCollector::findFile($img,array($dir,$this->cssPath,$this->wwwDir));
					if (filesize($source_file)<$this->maxSize) {
						$imgbinary = fread(fopen($source_file, "r"), filesize($source_file));
						$this->output = str_replace($img, Helpers::dataStream($imgbinary),$this->output);
					}
			}
		}
		return $this->output;
	}
}
