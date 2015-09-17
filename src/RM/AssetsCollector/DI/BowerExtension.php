<?php

namespace RM\AssetsCollector\DI;

use Nette\DI\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
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
			'bowerFile' => Helpers::expand('%appDir%', $builder->parameters) . '/../bower.json',
			'netteForms' => TRUE,
			'netteAjax' => TRUE,
		));

		if (isset($config['bowerFile'])) {

			$bowerDir = $this->getBowerDir($config['bowerFile']);
			$packages = $this->getPackages($bowerDir);

			foreach ($builder->findByType('RM\AssetsCollector\AssetsCollector') as $name => $def) {
				foreach ($def->getSetup() as $statement)
					if (in_array($statement->getEntity(), ['addPackages', 'setPackages']))
						foreach (array_keys($statement->arguments[0]) as $skipped)
							unset($packages[$skipped]);

				$def->addSetup('setPackages', array($packages));
				if ($config['netteAjax'] && isset($packages['nette.ajax.js'])) {
					foreach ($builder->findByTag('nette.form') as $service => $attr) {
						$builder->getDefinition($service)->addSetup('?->addPackages(\'nette.ajax.js\')', array('@' . $name));
					}
				} elseif ($config['netteForms'] && isset($packages['nette-forms'])) {
					foreach ($builder->findByTag('nette.form') as $service => $attr) {
						$builder->getDefinition($service)->addSetup('?->addPackages(\'nette.ajax.js\')', array('@' . $name));
					}
				}
				break;
			}

		}
	}


	/**
	 * Get array with packages description
	 * @param  string $bowerDir
	 * @return array
	 */
	protected function getPackages($bowerDir)
	{
		$separateCss = function($s) {
			return (substr($s, -4) === '.css');
		};
		$separateJs = function($s) {
			return (substr($s, -3) === '.js');
		};

		$packages = array();
		foreach (Finder::findFiles('bower.json')->from($bowerDir) as $path => $file) {
			$componentJson = Json::decode(file_get_contents($path));

			$fullPath = function($s) use ($path) {
				return pathinfo($path, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . $s;
			};

			$packages[$componentJson->name] =
			$packages[pathinfo(pathinfo($path, PATHINFO_DIRNAME), PATHINFO_BASENAME)] = array_filter(
				array(
					"css" => array_map($fullPath, array_filter(@(array)$componentJson->main, $separateCss)),
					"js" => array_map($fullPath, array_filter(@(array)$componentJson->main, $separateJs)),
					"extends" => array_values(array_flip(@(array)$componentJson->dependencies)),
				), function ($a) {
					return (!empty($a));
				}
			);
		}
		return $packages;
	}


	/**
	 * Get dir with Bower components.
	 * @param  string $bowerFilePath
	 * @return string
	 */
	protected function getBowerDir($bowerFilePath)
	{
		$builder = $this->getContainerBuilder();
		$bowerJson = Json::decode(file_get_contents($bowerFilePath));

		$bowerDirs = array();
		if (isset($bowerJson->directory)) {
			$bowerDirs = array(
				$bowerJson->directory,
				pathinfo($bowerFilePath, PATHINFO_DIRNAME) . '/' . $bowerJson->directory,
			);
		}
		$bowerDirs[] = Helpers::expand('%appDir%', $builder->parameters) . '/../bower_components';

		foreach ($bowerDirs as $bowerDir)
			if ($this->checkDir($bowerDir))
				return $bowerDir;
	}


	/**
	 * Check dir validity.
	 * @param  string $dir
	 * @return bool
	 */
	protected function checkDir($dir)
	{
		if (!is_dir($dir))
			throw new InvalidArgumentException("Path '$dir' is not directory.");
		if (!is_readable($dir))
			throw new InvalidArgumentException("Path '$dir' is not readable.");
		return TRUE;
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
