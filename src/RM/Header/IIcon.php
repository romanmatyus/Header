<?php

namespace RM\Header;


/*
 * @copyright (c) Roman Mátyus
 * @license MIT
 * @package Header
 */
interface IIcon
{
	public function __toString();
	public function getString() : string;
}
