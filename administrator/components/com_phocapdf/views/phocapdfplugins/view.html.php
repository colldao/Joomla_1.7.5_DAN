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
jimport( 'joomla.html.pane' );
jimport( 'joomla.application.component.view' );

class PhocaPDFCpViewPhocaPDFPlugins extends JView
{
	
	protected $items;
	
	function display($tpl = null) {
		
		$this->items		= $this->get('Items');
		
		// If there is one, select it
		if (isset($this->items[0]->extension_id) && (int)$this->items[0]->extension_id > 0) {
			$app	= JFactory::getApplication();
			$app->redirect(JRoute::_('index.php?option=com_phocapdf&view=phocapdfplugin&task=phocapdfplugin.edit&extension_id='.(int)$this->items[0]->extension_id, false));
			return;
		}
		
		JHTML::stylesheet( 'administrator/components/com_phocapdf/assets/phocapdf.css' );
		$this->addToolbar();
		echo JText::_('COM_PHOCAPDF_NO_PHOCAPDF_PLUGIN_INSTALLED');
		
		
	}
	
	
	protected function addToolbar() {
	
		$this->state		= $this->get('State');
		
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'phocapdfplugins.php';
		//$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo	= PhocaPDFPluginsHelper::getActions();
		
		JToolBarHelper::title(   JText::_( 'COM_PHOCAPDF_PLUGINS' ), 'pdf' );
		
		$bar = & JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Link', 'back', 'COM_PHOCAPDF_CONTROL_PANEL', 'index.php?option=com_phocapdf' );

		JToolBarHelper::divider();
		
		JToolBarHelper::help( 'screen.phocapdf', true );
		
	}
}
?>