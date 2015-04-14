<?php

namespace RM\AssetsCollector\DI;

use Nette\DI\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\InvalidArgumentException;

/**
 * Class for register bower packages into AssetsCollector.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class BowerExtension extends CompilerExtension
{
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$config = $this->getConfig(array(
			'bowerFile' => $builder->expand('%appDir%') . '/../bower.json',
		));

		$checkDir = function ($dir) {
			if (!is_dir($dir))
				throw new InvalidArgumentException("Path '$dir' is not directory.");
			if (!is_readable($dir))
				throw new InvalidArgumentException("Path '$dir' is not readable.");
			return TRUE;
		};

		if (isset($config['bowerFile'])) {
			$bowerJson = Json::decode(file_get_contents($config['bowerFile']));

			$bowerDirs = array();
			if (isset($bowerJson->directory)) {
				$bowerDirs = array(
					$bowerJson->directory,
					pathinfo($config['bowerFile'], PATHINFO_DIRNAME) . '/' . $bowerJson->directory,
				);
			}
			$bowerDirs[] = $builder->expand('%appDir%') . '/../bower_components';

			$separateCss = function($s) {
				return (substr($s, -4) === '.css');
			};
			$separateJs = function($s) {
				return (substr($s, -3) === '.js');
			};

			foreach ($bowerDirs as $bowerDir) {
				if ($checkDir($bowerDir)) {

					$packages = array();
					foreach (Finder::findFiles('bower.json')->from($bowerDir) as $path => $file) {
						$componentJson = Json::decode(file_get_contents($path));

						$fullPath = function($s) use ($path) {
							return pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $s;
						};
						
						$packages[$componentJson->name] = array_filter(
							array(
								"css" => array_map($fullPath, array_filter(@(array)$componentJson->main, $separateCss)),
								"js" => array_map($fullPath, array_filter(@(array)$componentJson->main, $separateJs)),
								"extends" => array_values(array_flip(@(array)$componentJson->dependencies)),
							), function ($a) {
								return (!empty($a));
							}
						);
					}
					break;
				}
			}
		}

		foreach ($builder->findByType('RM\AssetsCollector\AssetsCollector') as $def) {
			$def->addSetup('setPackages', array($packages));
		}
	}

	/**
	 * Register Bower packages in to AssetsCollector.
	 * @param \Nette\DI\Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('bower', new BowerExtension());
		};
	}
}
