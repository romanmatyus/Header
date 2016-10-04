<?php

namespace RM\Header;

/*
 * @copyright (c) Roman Mátyus
 * @license MIT
 * @package Header
 */
interface IIconFactory
{
	public function create(string $source) : IIcon;
}
