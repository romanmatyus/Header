<?php
namespace RM\AssetsCollector\Latte;

use Latte\Macros\MacroSet;
use Latte\Compiler;

/**
 * Class defined macros for AssetsCollector.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class JsCssMacros extends MacroSet
{
	/**
	 * Method install macros.
	 * @param	compiler Nette\Latte\Compiler
	 */
	public static function install(Compiler $compiler)
	{
		$macroSet = new static($compiler);
		$macroSet->addMacro('css', 'echo "<!-- assets: " . \Nette\Utils\Json::encode($presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addCss(%node.array,dirname(($presenter->template->getFile()===$template->getName())?$presenter->template->getFile():$template->getName()))) . " -->";');
		$macroSet->addMacro('js', 'echo "<!-- assets: " . \Nette\Utils\Json::encode($presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addJs(%node.array, dirname(($presenter->template->getFile()===$template->getName())?$presenter->template->getFile():$template->getName()))) . " -->";');
		$macroSet->addMacro('pack', 'echo "<!-- assets: " . \Nette\Utils\Json::encode($presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addPackages(%node.array)) . " -->"');
		$macroSet->addMacro('cssContent', 'ob_start()','$content = ob_get_contents(); ob_end_clean(); echo "<!-- assets: " . \Nette\Utils\Json::encode($presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addCssContent($content,__DIR__)) . " -->";');
		$macroSet->addMacro('jsContent', 'ob_start()','$content = ob_get_contents(); ob_end_clean(); echo "<!-- assets: " . \Nette\Utils\Json::encode($presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addJsContent($content,__DIR__)) . " -->";');
	}
}
