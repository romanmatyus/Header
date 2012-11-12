<?php
namespace RM\AssetsCollector;

use \Nette\Config\Configurator,
	\Nette\Config\Compiler,
	\Nette\Config\CompilerExtension;


/**
 * Class for register extension AssetsCollector.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class AssetsCollectorExtension extends CompilerExtension
{
	/**
	 * Method setings extension.
	 */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

		$config = $this->getConfig(array(
			'cssPath' => WWW_DIR.'/style/css',
			'jsPath' => WWW_DIR.'/style/js',
			'webTemp' => WWW_DIR.'/webtemp',
			'wwwDir' => WWW_DIR,
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
            ->setClass('\RM\AssetsCollector\Compilers\CssSimpleMinificator');

		$builder->addDefinition($this->prefix('imageToDataStream'))
            ->setClass('\RM\AssetsCollector\Compilers\ImageToDataStream')
			->addSetup('$cssPath', $config['cssPath'])
			->addSetup('$wwwDir', $config['wwwDir'])
			->addSetup('$maxSize', $config['maxSize']);

		$builder->addDefinition($this->prefix('imageReplacer'))
            ->setClass('\RM\AssetsCollector\Compilers\ImageReplacer')
			->addSetup('$cssPath', $config['cssPath'])
			->addSetup('$wwwDir', $config['wwwDir'])
			->addSetup('$webTemp', $config['webTemp']);

		$config = array_merge($config,array(
			'addCssCompiler' => array(
				'@' . $this->prefix('cssSimpleMinificator'),
				'@' . $this->prefix('imageToDataStream'),
				'@' . $this->prefix('imageReplacer'),
			),
		));

		$builder->addDefinition($this->prefix('collector'))
            ->setClass('\RM\AssetsCollector')
			->addSetup('$cssPath', $config['cssPath'])
			->addSetup('$jsPath', $config['jsPath'])
			->addSetup('$webTemp', $config['webTemp'])
			->addSetup('$removeOld', $config['removeOld'])
			->addSetup('setPackages', array($config['packages']))
			->addSetup('addPackages', array($config['addPackage']))
			->addSetup('addCssCompiler', array($config['addCssCompiler']))
			->addSetup('addJsCompiler', array($config['addJsCompiler']))
			->addSetup('$enabledCompilers', array($config['enabledCompilers']))
			->addSetup('addCss', array($config['addCss']))
			->addSetup('addJs', array($config['addJs']))
			->addSetup('$mergeFiles', array($config['mergeFiles']))
			->addSetup('checkRequirements');

		$builder->getDefinition('nette.latte')
			->addSetup('\RM\AssetsCollector\JsCssMacros::install(?->compiler)', array('@self'));
    }

	/**
	 * Register AssetsCollector to application.
	 * @param \Nette\Config\Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('assetsCollector', new AssetsCollectorExtension());
		};
	}
}
