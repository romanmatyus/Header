<?php

namespace RM\AssetsCollector\DI;

use Nette\DI\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;

/**
 * Class for register extension AssetsCollector.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class AssetsCollectorExtension extends CompilerExtension
{
	private $netteForms = FALSE;

	/**
	 * Method setings extension.
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$config = $this->getConfig(array(
			'cssPath' => $_SERVER['DOCUMENT_ROOT'].'/css',
			'jsPath' => $_SERVER['DOCUMENT_ROOT'].'/js',
			'webTemp' => $_SERVER['DOCUMENT_ROOT'].'/webtemp',
			'wwwDir' => $_SERVER['DOCUMENT_ROOT'],
			'maxSize' => 1024,
			'packages' => array(),
			'addCss' => array(),
			'addJs' => array(),
			'addPackage' => array(),
			'removeOld' => FALSE,
			'addCssCompiler' => array(),
			'addJsCompiler' => array(),
			'enabledCompilers' => array(),
			'mergeFiles' => false,
		));

		$builder->addDefinition($this->prefix('cssSimpleMinificator'))
			->setClass('RM\AssetsCollector\Compilers\CssSimpleMinificator');

		$builder->addDefinition($this->prefix('imageToDataStream'))
			->setClass('RM\AssetsCollector\Compilers\ImageToDataStream')
			->addSetup('$cssPath', array($config['cssPath']))
			->addSetup('$wwwDir', array($config['wwwDir']))
			->addSetup('$maxSize', array($config['maxSize']));

		$builder->addDefinition($this->prefix('imageReplacer'))
			->setClass('RM\AssetsCollector\Compilers\ImageReplacer')
			->addSetup('$cssPath', array($config['cssPath']))
			->addSetup('$wwwDir', array($config['wwwDir']))
			->addSetup('$webTemp', array($config['webTemp']));

		$config = array_merge($config,array(
			'addCssCompiler' => array(
				'@' . $this->prefix('cssSimpleMinificator'),
				'@' . $this->prefix('imageToDataStream'),
				'@' . $this->prefix('imageReplacer'),
			),
		));

		$builder->addDefinition($this->prefix('collector'))
			->setClass('RM\AssetsCollector\AssetsCollector')
			->addSetup('$cssPath', array($config['cssPath']))
			->addSetup('$jsPath', array($config['jsPath']))
			->addSetup('$webTemp', array($config['webTemp']))
			->addSetup('$wwwDir', array($config['wwwDir']))
			->addSetup('$removeOld', array($config['removeOld']))
			->addSetup('setPackages', array($config['packages']))
			->addSetup('addPackages', array($config['addPackage']))
			->addSetup('addCssCompiler', array($config['addCssCompiler']))
			->addSetup('addJsCompiler', array($config['addJsCompiler']))
			->addSetup('$enabledCompilers', array($config['enabledCompilers']))
			->addSetup('addCss', array($config['addCss']))
			->addSetup('addJs', array($config['addJs']))
			->addSetup('$mergeFiles', array($config['mergeFiles']))
			->addSetup('checkRequirements');

		$builder->addDefinition($this->prefix('factory'))
			->setImplement('RM\Header\IHeaderFactory');

		$self = $this;
		$registerToLatte = function (ServiceDefinition $def) use ($self) {
			$def
				->addSetup('?->onCompile[] = function($engine) { RM\AssetsCollector\Latte\JsCssMacros::install($engine->getCompiler()); }', array('@self'));
		};

		if ($builder->hasDefinition('nette.latteFactory')) {
			$registerToLatte($builder->getDefinition('nette.latteFactory'));
		}

		if ($builder->hasDefinition('nette.latte')) {
			$registerToLatte($builder->getDefinition('nette.latte'));
		}
	}

	/**
	 * Register AssetsCollector to application.
	 * @param \Nette\DI\Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('assetsCollector', new AssetsCollectorExtension());
		};
	}
}
