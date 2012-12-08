<?php
namespace RM;

use Nette\Object,
	Nette\FileNotFoundException,
	Nette\InvalidArgumentException,
	Nette\Utils\Finder;

/**
 * Class for collecting CSS and JS files in PHP framework Nette.
 *
 * @author Roman Mátyus
 * @copyright (c) Roman Mátyus 2012
 * @license MIT
 */
class AssetsCollector extends Object
{
	/** File type */
	const CSS = "css";

	/** File type */
	const JS = "js";

	/** @var array of attached css files */
	protected $css = array();

	/** @var string base path for css files */
	public $cssPath;

	/** @var array of attached js files */
	protected $js = array();

	/** @var string base path for js files */
	public $jsPath;

	/** @var string path for temporary folder */
	public $webTemp;

	/** @var boolean remove all old files? */
	public $removeOld;

	/** @var array functions for compile css files */
	public $cssCompiler = array();

	/** @var array functions for compile js files */
	public $jsCompiler = array();

	/** @var array packages */
	private $packages;

	/** @var array enabled compilers */
	public $enabledCompilers;

	/** @var array used packages of files */
	private $usedPackages;

	/** @var boolean merge files to one file? */
	public $mergeFiles;

	/**
	 * Add css files to header.
	 * @param	file string|array
	 * @param	dir null|string dir for find file by relative path
	 * @return	AssetsCollector
	 */
	public function addCss($file,$dir=null)
	{
		if (is_string($file)) {
			$f = self::findFile($file,array($this->cssPath,$dir));
			$fileNameOutput = $this->getTempFromFile($f,self::CSS);
			if (!in_array($fileNameOutput,$this->css))
				$this->css[] = $fileNameOutput;
		} elseif (is_array($file)) {
			foreach ($file as $item) {
				$f = self::findFile($item,array($this->cssPath,$dir));
				$fileNameOutput = $this->getTempFromFile($f,self::CSS);
				if (!in_array($fileNameOutput,$this->css))
					$this->css[] = $fileNameOutput;
			}
		}
		return $this;
	}

	/**
	 * Add js files to header.
	 * @param	file string|array
	 * @param	dir null|string direcory for find file by relative path
	 * @return	AssetsCollector
	 */
	public function addJs($file,$dir=null)
	{
		if (is_string($file)) {
			$f = self::findFile($file,array($this->jsPath,$dir));
			$fileNameOutput = $this->getTempFromFile($f,self::JS);
			if (!in_array($fileNameOutput,$this->js))
				$this->js[] = $fileNameOutput;
		} elseif (is_array($file)) {
			foreach ($file as $item) {
				$f = self::findFile($item,array($this->jsPath,$dir));
				$fileNameOutput = $this->getTempFromFile($f,self::JS);
				if (!in_array($fileNameOutput,$this->js))
					$this->js[] = $fileNameOutput;
			}
		}
		return $this;
	}

	/**
	 * Add CSS files to header from plain entry.
	 * @param	content string
	 * @return	AssetsCollector
	 */
	public function addCssContent($content,$dir=null)
	{
		$fileNameOutput = $this->getTempFromContent($content,$dir=null,self::CSS);
		if (!in_array($fileNameOutput,$this->css))
			$this->css[] = $fileNameOutput;
		return $this;
	}

	/**
	 * Add JS files to header from plain entry.
	 * @param	content string
	 * @param	dirs null|array where searches
	 * @return	AssetsCollector
	 */
	public function addJsContent($content,$dir=null)
	{
		$fileNameOutput = $this->getTempFromContent($content,$dir=null,self::JS);
		if (!in_array($fileNameOutput,$this->js))
			$this->js[] = $fileNameOutput;
		return $this;
	}

	/**
	 * Find file in several directories.
	 * @param	filename string name of file
	 * @param	dirs null|array where searches
	 * @return	string findet file
	 */
	public static function findFile($filename, array $dirs = NULL)
	{
		if (!empty($dirs))
			foreach ($dirs as $dir)
				if (file_exists($dir."/".$filename))
					return realpath($dir."/".$filename);
		if (file_exists($filename))
			return realpath($filename);
		throw new FileNotFoundException("File '" . $filename . "' not found.");
	}

	/**
	 * Method for add string in to file name e.g. filename.css => filename.string.css.
	 * @param	filename string
	 * @param	string string
	 * @return	string new filename with string in filename
	 */
	private function addToFileName($filename,$string)
	{
		$path = explode("/",$filename);
		$filename = array_pop($path);
		$filename = explode(".",$filename);
		$extension = array_pop($filename);
		$filename[] = $string;
		$filename = implode(".",$filename).".".$extension;
		return implode("/",$path)."/".$filename;
	}

	/**
	 * Create temporary filename of CSS or JS files from path.
	 * @param	source string filename with complete path
	 * @param	type string type of files self::CSS or self::JS
	 * @return	filename with complete path in temporary directory
	 */
	private function getTempFromFile($source,$type)
	{
		$content = file_get_contents($source);
		$md5 = md5($content);
		
		$filename = explode("/",self::addToFileName($source,$md5));
		$fileNameOutput = array_pop($filename);
		
		if (!file_exists($this->webTemp."/".$fileNameOutput)) {
			// run compilers
			$compile_function = 'compile'.$type;
			$content = $this->$compile_function($content,dirname($source));
			file_put_contents($this->webTemp."/".$fileNameOutput,$content);
		}

		// Remove all old versions
		$this->removeAllOldFiles($source, $fileNameOutput);

		// return real path
		return substr($this->webTemp,strlen(WWW_DIR))."/".$fileNameOutput;
	}

	/**
	 * Create temporary filename of CSS or JS files from content.
	 * @param	content string content of generated file
	 * @param	dirs null|array where searches
	 * @param	type string type of files self::CSS or self::JS
	 * @return filename with complete path in temporary directory
	 */
	private function getTempFromContent($content,$dir=null,$type)
	{
		if (strlen($content)===0)
			throw new InvalidArgumentException("Content of generated file can not be empty.");
		$md5 = md5($content);
		
		$fileNameOutput = $md5.".".$type;
		if (!file_exists($this->webTemp."/".$fileNameOutput)) {
			// run compilers
			$compile_function = 'compile'.$type;
			$content = $this->$compile_function($content,$dir);
			file_put_contents($this->webTemp."/".$fileNameOutput,$content);
		}

		// return real path
		return substr($this->webTemp,strlen(WWW_DIR))."/".$fileNameOutput;
	}

	/**
	 * Get all CSS temporary files for use in header.
	 * @return	array of all files includet to header
	 */
	public function getCss()
	{
		if ($this->mergeFiles)
			$this->css = array($this->mergeFiles($this->css,self::CSS));
		$css_output = array();
		foreach ($this->css as $source => $temp) {
			$css_output[] = $temp;
		}
		return $css_output;
	}

	/**
	 * Get all JS temporary files for use in header.
	 * @return	array of all files includet to header
	 */
	public function getJs()
	{
		if ($this->mergeFiles)
			$this->js = array($this->mergeFiles($this->js,self::JS));
		$js_output = array();
		foreach ($this->js as $source => $temp) {
			$js_output[] = $temp;
		}
		return $js_output;
	}

	/**
	 * Remove all old files.
	 * @param	source string source file
	 * @param	fileNameOutput string of compiled source file
	 */
	private function removeAllOldFiles($source, $fileNameOutput)
	{
		if ($this->removeOld) {
			$d = explode("/",$source);
			$f = explode(".",array_pop($d));
			$ext = array_pop($f);
			foreach (Finder::findFiles(implode(".",$f).'*.'.$ext)
				->exclude($fileNameOutput)->from($this->webTemp) as $file) {
				unlink($file->getRealPath());
			}
		}
	}

	/**
	 * Check requirements.
	 */
	public function checkRequirements()
	{
		if (is_null($this->webTemp))
			throw new InvalidArgumentException("Directory for temporary files is not defined.");
		if (!is_dir($this->webTemp))
			throw new FileNotFoundException($this->webTemp." is not directory.");
		if (!is_writable($this->webTemp))
			throw new InvalidArgumentException("Directory '".$this->webTemp."' is not writeable.");
	}

	/**
	 * Apply enabled CSS file compilers.
	 * @param	content string input
	 * @param	dirs null|array where searches
	 * @return	string content after compile
	 */
	private function compileCss($content,$dir=null)
	{
		\Nette\diagnostics\Debugger::bardump("css compile");
		foreach ($this->cssCompiler as $name => $compiler) {
			if (in_array($name,$this->enabledCompilers))
				$content = $compiler($content,$dir);
		}
		return $content;
	}

	/**
	 * Apply enabled JS file compilers.
	 * @param	content string input
	 * @param	dirs null|array where searches
	 * @return	string content after compile
	 */
	private function compileJs($content,$dir=null)
	{
		foreach ($this->jsCompiler as $name => $compiler) {
			if (in_array($name,$this->enabledCompilers))
				$content = $compiler($content,$dir);
		}
		return $content;
	}

	/**
	 * Add CSS compiler.
	 * @param	array with items \RM\AssetsCollector\Compilers\IAssetsCompiler
	 */
	public function addCssCompiler(array $compilers)
	{
		foreach ($compilers as $compiler) {
			$name = explode("\\",get_class($compiler));
			$name = lcfirst(array_pop($name));
			if ($compiler instanceof \RM\AssetsCollector\Compilers\IAssetsCompiler)
				$this->cssCompiler[$name] = callback($compiler, 'compile');
			else
				throw new InvalidArgumentException("Compiler must be instance of \RM\AssetsCollector\Compilers\IAssetsCompiler");
		}
	}

	/**
	 * Add JS compiler.
	 * @param	compilers array with items \RM\AssetsCollector\Compilers\IAssetsCompiler
	 */
	public function addJsCompiler(array $compilers)
	{
		foreach ($compilers as $compiler) {
			$name = explode("\\",get_class($compiler));
			$name = lcfirst(array_pop($name));
			if ($compiler instanceof \RM\AssetsCollector\Compilers\IAssetsCompiler)
				$this->jsCompiler[$name] = callback($compiler, 'compile');
			else
				throw new InvalidArgumentException("Compiler must be instance of \RM\AssetsCollector\Compilers\IAssetsCompiler");
		}
	}

	/**
	 * Set package to service.
	 * @param	name string name of package
	 * @param	extends null|array of packages where this package extends
	 * @param	css null|array of included CSS files
	 * @param	js null|array of included JS files
	 */
	public function setPackage($name, array $extends = null, array $css = null, array $js = null)
	{
		$this->packages[$name] = new \RM\AssetsCollector\Package($name, $extends, $css, $js);
	}

	/**
	 * Set packages to service from array.
	 * @param	packages array
	 */
	public function setPackages(array $packages)
	{
		foreach ($packages as $name => $details) {
			(isset($details['extends']))?$extends=$details['extends']:$extends=null;
			(isset($details['css']))?$css=$details['css']:$css=null;
			(isset($details['js']))?$js=$details['js']:$js=null;
			if ($css===null AND $js===null and $extends===null)
				throw new InvalidArgumentException("Package $name is empty.");
			if ($css===null AND $js===null and count($extends)<=1)
				throw new InvalidArgumentException("Package $name duplicated package $extends[0].");
			$this->setPackage ($name, $extends, $css, $js);
		}
	}

	/**
	 * Add package to service.
	 * @param	package string with name of package
	 */
	public function addPackage($package)
	{
		$this->usedPackages[] = $package;
		$dependecies = $this->getDependecies($package);
		if(!empty($dependecies['css']))
			foreach ($dependecies['css'] as $css)
				$this->addCss($css);
		if(!empty($dependecies['js']))
			foreach ($dependecies['js'] as $js)
				$this->addJs($js);
		$package = $this->packages[$package];
	}

	/**
	 * Get all dependencies.
	 * @param	string name of package
	 * @return	array of all CSS and JS dependecies for package
	 */
	public function getDependecies($package)
	{
		$dependencies = array("css"=>array(),"js"=>array());
		$package = $this->packages[$package];
		if(!empty($package->extends))
			foreach ($package->extends as $extend) {
				$ex = $this->getDependecies($extend);
					$dependencies['css'] = array_merge($dependencies['css'],$ex['css']);
					$dependencies['js'] = array_merge($dependencies['js'],$ex['js']);
			}
		if(!empty($package->css))
			foreach ($package->css as $css)
				$dependencies['css'][] = $css;
		if(!empty($package->js))
			foreach ($package->js as $js)
				$dependencies['js'][] = $js;
		$dependencies['css'] = array_unique($dependencies['css']);
		$dependencies['js'] = array_unique($dependencies['js']);
		return $dependencies;
	}

	/**
	 * Add packages to service.
	 * @param	packages string|array
	 * @return	AssetsCollector
	 */
	public function addPackages($packages)
	{
		if (is_string($packages)) {
			$this->addPackage($packages);
		} elseif (is_array($packages)) {
			foreach ($packages as $package) {
				$this->addPackage($package);
			}
		}
		return $this;
	}

	/**
	 * Merge files to one file.
	 * @param	files array of files for merged
	 * @param	type string type of files self::CSS or self::JS
	 * @return	path of merged files
	 */
	public function mergeFiles(array $files, $type)
	{
		$content = "";

		foreach ($files as $file)
			$content .= file_get_contents(WWW_DIR.$file);

		$fileNameOutput = md5($content).".".$type;

		if (!file_exists($this->webTemp."/".$fileNameOutput))
			file_put_contents($this->webTemp."/".$fileNameOutput,$content);

		return substr($this->webTemp,strlen(WWW_DIR))."/".$fileNameOutput;
	}
}
