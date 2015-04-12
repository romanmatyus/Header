<?php

namespace RM\AssetsCollector\Compilers;

use Nette\FileNotFoundException;
use Nette\InvalidArgumentException;
use RM\AssetsCollector;

/**
 * CSS compiler where replace images in content to real images.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class ImageReplacer extends BaseCssAssetsCompiler implements IAssetsCompiler
{
	/**
	 * Get compiled content.
	 * @param  string $input
	 * @param  string $dir
	 * @return string
	 */
	public function compile($input, $dir = NULL)
	{
		$this->checkRequirements();
		$this->input = $this->output = $input;
		$images = $this->getImages();
		if ($images) {
			foreach($images as $img) {
				if ($img[0]!=="/") {
					$source_file = AssetsCollector::findFile($img,array($dir,$this->cssPath,$this->wwwDir));
					$hash = md5_file($source_file);
					$f = explode(".",$source_file);
					$ext = array_pop($f);
					$output_file = $this->webTemp."/".$hash.".".$ext;
					if (!file_exists($output_file))
						copy($source_file,$output_file);
					$this->output = str_replace($img,substr($output_file,strlen(realpath($this->wwwDir))),$this->output);
				}
			}
		}
		return $this->output;
	}

	/**
	 * Check requirements.
	 */
	private function checkRequirements()
	{
		if (is_null($this->webTemp))
			throw new InvalidArgumentException("Directory for temporary files is not defined.");
		if (!is_dir($this->webTemp))
			throw new FileNotFoundException($this->webTemp." is not directory.");
		if (!is_writable($this->webTemp))
			throw new InvalidArgumentException("Directory '".$this->webTemp."' is not writeable.");
	}
}
