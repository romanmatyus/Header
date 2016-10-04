<?php

namespace RM\Header;

use Nette\Caching\IStorage;
use Nette\Caching\Cache;


/*
 * @copyright (c) Roman Mátyus
 * @license MIT
 * @package Header
 */
class IconFactory implements IIconFactory
{

	/** @var string Name of class with IIcon interface */
	public $class = 'RM\Header\Icon';

	/** @var [] */
	public $config;

	/** @var string path for temporary folder */
	protected $webTemp;

	/** @var string path for base www folder */
	protected $wwwDir;

	/** @var Cache */
	protected $cache;


	public function __construct(string $webTemp, string $wwwDir, IStorage $storage, array $config = NULL)
	{
		$this->webTemp = $webTemp;
		$this->wwwDir = $wwwDir;
		$this->cache = new Cache($storage);
		$this->config = ($config) ?? [
			'meta' => [
				'theme-color' => '#ffffff',
			],
		];
	}

	public function create(string $source) : IIcon
	{
		$class = $this->class;
		return new $class($this->webTemp, $this->wwwDir, $source, $this->cache, $this->config);
	}
}
