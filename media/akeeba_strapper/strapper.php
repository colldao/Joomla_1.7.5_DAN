<?php
/**
 * Akeeba Strapper
 * A handy distribution of namespaced jQuery, jQuery UI and Twitter
 * Bootstrapper for use with Akeeba components.
 */
 
defined('_JEXEC') or die();

if(!defined('FOF_INCLUDED')) {
	include_once JPATH_SITE.'/libraries/fof/include.php';
}
 
class AkeebaStrapper {
 	/** @var bool True when jQuery is already included */
 	public static $_includedJQuery = false;
 	
 	/** @var bool True when jQuery UI is already included */
 	public static $_includedJQueryUI = false;
 	
 	/** @var bool True when Bootstrap is already included */
 	public static $_includedBootstrap = false;
 	
 	/** @var array List of URLs to Javascript files */
 	public static $scriptURLs = array();
 	
 	/** @var array List of script definitions to include in the head */
 	public static $scriptDefs = array();
 	
 	/** @var array List of URLs to CSS files */
 	public static $cssURLs = array();
 	
 	/** @var array List of CSS definitions to include in the head */
 	public static $cssDefs = array();
 	
 	/** @var string The jQuery UI theme to use, default is 'smoothness' */
 	protected static $jqUItheme = 'smoothness';
 	
	/** @var string A query tag to append to CSS and JS files for versioning purposes */
	public static $tag = null;
	
	/**
	 * Is this something running under the CLI mode?
	 * @staticvar bool|null $isCli
	 * @return null 
	 */
	public static function isCli()
	{
		static $isCli = null;
		if(is_null($isCli)) {
			try {
				if(is_null(JFactory::$application)) {
					$isCLI = true;
				} else {
					$isCLI = version_compare(JVERSION, '1.6.0', 'ge') ? (JFactory::getApplication() instanceof JException) : false;
				}
			} catch(Exception $e) {
				$isCLI = true;
			}		
		}
		return $isCli;
	}
	
 	/**
 	 * Loads our namespaced jQuery, accessible through akeeba.jQuery
 	 */
 	public static function jQuery()
 	{
		if(self::isCli()) return;
		
 		self::$_includedJQuery = true;
 		
 		self::$scriptURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/js/akeebajq.js');
 	}
 	
 	/**
 	 * Sets the jQuery UI theme to use. It must be the name of a subdirectory of
 	 * media/akeeba_strapper/css or templates/<yourtemplate>/media/akeeba_strapper/css
 	 *
 	 * @param $theme string The name of the subdirectory holding the theme
 	 */
 	public static function setjQueryUItheme($theme)
 	{
		if(self::isCli()) return;
		
 		self::$jqUItheme = $theme;
 	}
 	
	/**
	 * Loads our namespaced jQuery UI and its stylesheet
	 */
 	public static function jQueryUI()
 	{
		if(self::isCli()) return;
		
 		if(!self::$_includedJQuery) {
 			self::jQuery();
 		}
 	
 		self::$_includedJQueryUI = true;
 		$theme = self::$jqUItheme;
 		
 		self::$scriptURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/js/akeebajqui.js');
 		self::$cssURLs[] = FOFTemplateUtils::parsePath("media://akeeba_strapper/css/$theme/theme.css");
 	}
 	
 	/**
 	 * Loads our namespaced Twitter Bootstrap. You have to wrap the output you want style
 	 * with an element having the class akeeba-bootstrap added to it.
 	 */
 	public static function bootstrap()
 	{
		if(self::isCli()) return;
		
 		if(!self::$_includedJQuery) {
 			self::jQuery();
 		}
 		
 		self::$scriptURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/js/bootstrap.min.js');
 		self::$cssURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/css/bootstrap.min.css');
 		self::$cssURLs[] = FOFTemplateUtils::parsePath('media://akeeba_strapper/css/strapper.css');
 	}
 	
 	/**
 	 * Adds an arbitraty Javascript file.
 	 *
 	 * @param $path string The path to the file, in the format media://path/to/file
 	 */
 	public static function addJSfile($path)
 	{
 		self::$scriptURLs[] = FOFTemplateUtils::parsePath($path);
 	}
 	
 	/**
 	 * Add inline Javascript
 	 *
 	 * @param $script string Raw inline Javascript
 	 */
	public static function addJSdef($script)
	{
		self::$scriptDefs[] = $script;
	}
	
	/**
	 * Adds an arbitraty CSS file.
	 *
	 * @param $path string The path to the file, in the format media://path/to/file
	 */
 	public static function addCSSfile($path)
 	{
 		self::$cssURLs[] = FOFTemplateUtils::parsePath($path);
 	}
 	
	/**
	 * Add inline CSS
	 *
	 * @param $style string Raw inline CSS
	 */
 	public static function addCSSdef($style)
 	{
 		self::$cssDefs[] = $style;
 	}
 }
 
 /**
  * This is a workaround which ensures that Akeeba's namespaced JavaScript and CSS will be loaded
  * wihtout being tampered with by any system pluign. Moreover, since we are loading first, we can
  * be pretty sure that namespacing *will* work and we won't cause any incompatibilities with third
  * party extensions loading different versions of these GUI libraries.
  *
  * This code works by registering a system plugin hook :) It will grab the HTML and drop its own
  * JS and CSS definitions in the head of the script, before anything else has the chance to run.
  *
  * Peace.
  */
 function AkeebaStrapperLoader()
 {
 	// If there are no script defs, just go to sleep
 	if(empty(AkeebaStrapper::$scriptURLs) && empty(AkeebaStrapper::$scriptDefs) ) return;
	
	// Get the query tag
	$tag = AkeebaStrapper::$tag;
	if(empty($tag)) {
		$tag = '';
	} else {
		$tag = '?'.ltrim($tag,'?');
	}
 
 	$myscripts = '';
	
	$buffer = JResponse::getBody();
 	
 	if(!empty(AkeebaStrapper::$scriptURLs)) foreach(AkeebaStrapper::$scriptURLs as $url)
 	{
		if(basename($url) == 'bootstrap.min.js') {
			// Special case: check that nobody else is using bootstrap[.min].js on the page.
			$scriptRegex="/<script [^>]+(\/>|><\/script>)/i";
			$jsRegex="/([^\"\'=]+\.(js)(\?[^\"\']*){0,1})[\"\']/i";
			preg_match_all($scriptRegex, $buffer, $matches);
			$scripts=@implode('',$matches[0]);
			preg_match_all($jsRegex,$scripts,$matches);
			$skip = false;
			foreach( $matches[1] as $scripturl ) {
				$scripturl = basename($scripturl);
				if(in_array($scripturl, array('bootstrap.min.js','bootstrap.js'))) {
					$skip = true;
				}
			}
			if($skip) continue;
		}
 		$myscripts .= '<script type="text/javascript" src="'.$url.$tag.'"></script>'."\n";
 	}
 	
 	if(!empty(AkeebaStrapper::$scriptDefs))
 	{
 		$myscripts .= '<script type="text/javascript" language="javascript">'."\n";
 		foreach(AkeebaStrapper::$scriptDefs as $def)
 		{
 			$myscripts .= $def."\n";
 		}
 		$myscripts .= '</script>'."\n";
 	}
 	
 	if(!empty(AkeebaStrapper::$cssURLs)) foreach(AkeebaStrapper::$cssURLs as $url)
 	{
 		$myscripts .= '<link type="text/css" rel="stylesheet" href="'.$url.$tag.'" />'."\n";
 	}
 	
 	if(!empty(AkeebaStrapper::$cssDefs))
 	{
 		$myscripts .= '<style type="text/css">'."\n";
 		foreach(AkeebaStrapper::$cssDefs as $def)
 		{
 			$myscripts .= $def."\n";
 		}
 		$myscripts .= '</style>'."\n";
 	}
 	
 	$pos = strpos($buffer, "<head>");
 	if($pos > 0)
 	{
 		$buffer = substr($buffer, 0, $pos + 6).$myscripts.substr($buffer, $pos + 6);
 		JResponse::setBody($buffer);
 	}
 }
 
// Add our pseudo-plugin to the application event queue
if(!AkeebaStrapper::isCli()) {
	$app = JFactory::getApplication();
	$app->registerEvent('onAfterRender', 'AkeebaStrapperLoader');
}