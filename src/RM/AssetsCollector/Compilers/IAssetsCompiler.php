<?php

namespace RM\AssetsCollector\Compilers;

/**
 * Interface for CSS/JS file compilers.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
interface IAssetsCompiler
{
	/**
	 * Get compiled content.
	 * @param  string $input
	 * @param  string $dir
	 * @return string
	 */
	public function compile($input, $dir = NULL);
}
