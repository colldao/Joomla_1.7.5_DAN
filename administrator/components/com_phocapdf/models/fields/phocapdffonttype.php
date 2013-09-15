<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die();


class JFormFieldPhocaPDFFontType extends JFormField
{
	protected $type 		= 'PhocaPDFFontType';
	protected $path			= null;

	protected function getInput() {
		
		$font = $this->_getXmlFiles();
		$options = array();
		if (!empty($font)) {
			foreach($font as $option) {
				if (isset($option->tag)) {
					$val	= $option->tag;
					$text	= $option->tag;
					$options[] = JHTML::_('select.option', $val, JText::_($text));
				}
			}
		}
		if (empty($options)) {
			return JText::_('COM_PHOCAPDF_NO_PHOCAPDF_FONT_FOUND');
		} else {
			return JHTML::_('select.genericlist',  $options, $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id);
		}
		
	}
	
	protected function _getXmlFiles() {
		$xmlFiles 		= JFolder::files($this->_getPath(), '.xml$', 1, true);
		$font			= array();
		
		// If at least one xml file exists
		if (count($xmlFiles) > 0) {
			foreach ($xmlFiles as $key => $value) {
				
				$xml = $this->_isManifest($value);	
				if(!is_null($xml->document->children())) {
					foreach ($xml->document->children() as $key => $value) {
						if ($value->_name == 'tag') {
							$font[]->tag = $value->_data;
						}
					}
				}
			}
		}
		return $font;
	}
	
	protected function &_isManifest($file) {
		// Initialize variables
		$null	= null;
		$xml	=& JFactory::getXMLParser('Simple');

		// If we cannot load the xml file return null
		if (!$xml->loadFile($file)) {
			// Free up xml parser memory and return null
			unset ($xml);
			return $null;
		}
		
		$root =& $xml->document;
		if (!is_object($root) || ($root->name() != 'install' )) {
			// Free up xml parser memory and return null
			unset ($xml);
			return $null;
		}
		return $xml;
	}
	
	protected function _getPath() {
		if (empty($this->_path)) {
			$this->_path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_phocapdf'.DS.'fonts';
		}
		return $this->_path;
	}
}
?>