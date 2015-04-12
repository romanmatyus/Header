<?php

namespace RM\Header;

/**
 * @copyright (c) Roman Mátyus 2015
 * @license MIT
 * @package Header
 */
interface IHeaderFactory
{
    /** @return Header */
    function create();
}