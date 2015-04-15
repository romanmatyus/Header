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
		$macroSet->addMacro('css', '$presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addCss(%node.array,dirname(($presenter->template->getFile()===$template->getFile())?$presenter->template->getFile():$template->getFile()));');
		$macroSet->addMacro('js', '$presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addJs(%node.array,dirname(($presenter->template->getFile()===$template->getFile())?$presenter->template->getFile():$template->getFile()));');
		$macroSet->addMacro('pack', '$presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addPackages(%node.array)');
		$macroSet->addMacro('cssContent', 'ob_start()','$content = ob_get_contents(); ob_end_clean(); $presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addCssContent($content,__DIR__);');
		$macroSet->addMacro('jsContent', 'ob_start()','$content = ob_get_contents(); ob_end_clean(); $presenter->getContext()->getByType(\'RM\\AssetsCollector\\AssetsCollector\')->addJsContent($content,__DIR__);');
	}
}
