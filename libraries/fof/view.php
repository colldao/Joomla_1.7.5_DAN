<?php
/**
 *  @package FrameworkOnFramework
 *  @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * Guess what? JView is an interface in Joomla! 3.0. Holly smoke, Batman! 
 */
if(!class_exists('FOFWorksAroundJoomlaToGetAView')) {
	if(interface_exists('JModel')) {
		abstract class FOFWorksAroundJoomlaToGetAView extends JViewLegacy {}
	} else {
		class FOFWorksAroundJoomlaToGetAView extends JView {}
	}
}

/**
 * FrameworkOnFramework View class
 * 
 * FrameworkOnFramework is a set of classes which extend Joomla! 1.5 and later's
 * MVC framework with features making maintaining complex software much easier,
 * without tedious repetitive copying of the same code over and over again.
 */
abstract class FOFView extends FOFWorksAroundJoomlaToGetAView
{
	static $renderers = array();
	
	protected $config = array();
	
	protected $input = array();
	
	protected $rendererObject = null;
	
	public function  __construct($config = array()) {
		parent::__construct($config);
		
		// Get the input
		if(array_key_exists('input', $config)) {
			$this->input = $config['input'];
		} else {
			$this->input = JRequest::get('default', 3);
		}
		
		// Get the component name
		if(array_key_exists('input', $config)) {
			$component = FOFInput::getCmd('option','',$config['input']);
		}
		if(array_key_exists('option', $config)) if($config['option']) $component = $config['option'];
		$config['option'] = $component;
		
		// Get the view name
		if(array_key_exists('input', $config)) {
			$view = FOFInput::getCmd('view','',$config['input']);
		}
		if(array_key_exists('view', $config)) if($config['view']) $view = $config['view'];
		$config['view'] = $view;
		
		// Set the component and the view to the input array
		if(array_key_exists('input', $config)) {
			FOFInput::setVar('option', $config['option'], $config['input']);
			FOFInput::setVar('view', $config['view'], $config['input']);
		}
		
		// Set the view name
		if (array_key_exists('name', $config))  {
			$this->_name = $config['name'];
		} else {
			$this->_name = $config['view'];
		}
		
		// Set a base path for use by the view
		if (array_key_exists('base_path', $config)) {
			$this->_basePath	= $config['base_path'];
		} else {
			$isAdmin = version_compare(JVERSION, '1.6.0', 'ge') ? (!JFactory::$application ? false : JFactory::getApplication()->isAdmin()) : JFactory::getApplication()->isAdmin();
			$this->_basePath	= ($isAdmin ? JPATH_ADMINISTRATOR : JPATH_COMPONENT).'/'.$config['option'];
		}
		
		// Set the default template search path
		if (array_key_exists('template_path', $config)) {
			// User-defined dirs
			$this->_setPath('template', $config['template_path']);
		} else {
			$altView = FOFInflector::isSingular($this->getName()) ? FOFInflector::pluralize($this->getName()) : FOFInflector::singularize($this->getName());
			$this->_setPath('template', $this->_basePath . '/views/' . $altView . '/tmpl');
			$this->_addPath('template', $this->_basePath . '/views/' . $this->getName() . '/tmpl');
		}
		
		// Set the default helper search path
		if (array_key_exists('helper_path', $config)) {
			// User-defined dirs
			$this->_setPath('helper', $config['helper_path']);
		} else {
			$this->_setPath('helper', $this->_basePath . '/helpers');
		}

		$this->config = $config;
		
		$app = JFactory::getApplication();
		if (isset($app))
		{
			$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $component);
			$fallback = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $this->getName();
			$this->_addPath('template', $fallback);
		}
	}
	
	/**
	 * Loads a template given any path. The path is in the format:
	 * [admin|site]:com_foobar/viewname/templatename
	 * e.g. admin:com_foobar/myview/default
	 * @param string $path 
	 * @param array $forceParams A hash array of variables to be extracted in the local scope of the template file
	 */
	public function loadAnyTemplate($path = '', $forceParams = array())
	{
		$template = JFactory::getApplication()->getTemplate();
		if(version_compare(JVERSION, '1.6.0', 'ge')) {
			$layoutTemplate = $this->getLayoutTemplate();
		}
		
		// Parse the path
		$templateParts = $this->_parseTemplatePath($path);
		
		// Get the default paths
		$paths = array();
		$paths[] = ($templateParts['admin'] ? JPATH_ADMINISTRATOR : JPATH_SITE).'/templates/'.
			$template.'/html/'.$templateParts['component'].'/'.$templateParts['view'];
		$paths[] = ($templateParts['admin'] ? JPATH_ADMINISTRATOR : JPATH_SITE).'/components/'.
			$templateParts['component'].'/views/'.$templateParts['view'].'/tmpl';
		
		// Look for a template override
		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$apath = array_shift($paths);
			array_unshift($paths, str_replace($template, $layoutTemplate, $apath));
		}
		
		$filetofind = $templateParts['template'].'.php';
		jimport('joomla.filesystem.path');
		$this->_tempFilePath = JPath::find($paths, $filetofind);
		if($this->_tempFilePath) {
			// Unset from local scope
			unset($template); unset($layoutTemplate); unset($paths); unset($path);
			unset($filetofind);
			
			// Never allow a 'this' property
			if (isset($this->this)) {
				unset($this->this);
			}
			
			// Force parameters into scope
			if(!empty($forceParams)) {
				extract($forceParams);
			}
			
			// Start capturing output into a buffer
			ob_start();
			// Include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_tempFilePath;

			// Done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		} else {
			return JError::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $path));
			return false;
		}
	}
	
	private function _parseTemplatePath($path = '')
	{
		$parts = array(
			'admin'		=> 0,
			'component'	=> $this->config['option'],
			'view'		=> $this->config['view'],
			'template'	=> 'default'
		);
		
		if(substr($path,0,6) == 'admin:') {
			$parts['admin'] = 1;
			$path = substr($path,6);
		} elseif(substr($path,0,5) == 'site:') {
			$path = substr($path,5);
		}
		
		if(empty($path)) return;
		
		$pathparts = explode('/', $path, 3);
		switch(count($pathparts)) {
			case 3:
				$parts['component'] = array_shift($pathparts);
			
			case 2:
				$parts['view'] = array_shift($pathparts);
			
			case 1:
				$parts['template'] = array_shift($pathparts);
				break;
		}
		
		return $parts;
	}
	
	/**
	 * Get the renderer object for this view
	 * @return FOFRenderAbstract
	 */
	public function &getRenderer()
	{
		if(!($this->rendererObject instanceof FOFRenderAbstract)) {
			$this->rendererObject = $this->findRenderer();
		}
		return $this->rendererObject;
	}
	
	/**
	 * Sets the renderer object for this view
	 * @param FOFRenderAbstract $renderer 
	 */
	public function setRenderer(FOFRenderAbstract &$renderer)
	{
		$this->rendererObject = $renderer;
	}
	
	/**
	 * Finds a suitable renderer
	 * 
	 * @return FOFRenderAbstract
	 */
	protected function findRenderer()
	{
		// Try loading the stock renderers shipped with FOF
		if(empty(self::$renderers) || !class_exists('FOFRenderJoomla', false)) {
			$path = dirname(__FILE__);
			$renderFiles = JFolder::files($path, 'render.');
			if(!empty($renderFiles)) {
				foreach($renderFiles as $filename) {
					if($filename == 'render.abstract.php') continue;
					@include_once $path.'/'.$filename;
					$camel = FOFInflector::camelize($filename);
					$className = 'FOFRender'.  ucfirst(FOFInflector::getPart($camel, 1));
					$o = new $className;
					self::registerRenderer($o);
				}
			}
		}
		
		// Try to detect the most suitable renderer
		$o = null;
		$priority = 0;
		if(!empty(self::$renderers)) {
			foreach(self::$renderers as $r) {
				$info = $r->getInformation();
				if(!$info->enabled) continue;
				if($info->priority > $priority) {
					$priority = $info->priority;
					$o = $r;
				}
			}
		}
		
		// Return the current renderer
		return $o;
	}
	
	public static function registerRenderer(FOFRenderAbstract &$renderer)
	{
		self::$renderers[] = $renderer;
	}
}
