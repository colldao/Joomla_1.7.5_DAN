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
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport( 'joomla.html.pane' );
class PhocaPDFCpViewPhocaPDFPlugin extends JView
{
	protected $item;
	protected $items;
	protected $form;
	protected $state;
	protected $tmpl;

	public function display($tpl = null)
	{
		$this->state	= $this->get('State');
		$this->items	= $this->get('Items');
		$this->item		= $this->get('Item');	
		$this->form		= $this->get('Form');
		
		JHTML::stylesheet( 'administrator/components/com_phocapdf/assets/phocapdf.css' );

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		// Plugins
		//$this->tmpl['id']	= JRequest::getVar( 'id', 0, '', 'int' );
		$this->tmpl['id']	= $this->state->get('phocapdfplugin.id');
		
		$i = 0;
		
		foreach ($this->items as $key => $value) {
		
			if ((int)$this->tmpl['id'] > 0) {
				if ($value->extension_id == (int)$this->tmpl['id']) {
					$value->current = 'class="current"';
				} else {
					$value->current = '';
				}
			} else {
				if ($i == 0) {
					$value->current = 'class="current"';
					$this->tmpl['id'] 	= (int)$value->extension_id;
				} else {
					$value->current = '';
				}
			}
			$value->name = str_replace('Phoca PDF - ', '', $value->name);
			$link		 = 'index.php?option=com_phocapdf&view=phocapdfplugin&task=phocapdfplugin.edit&extension_id='.(int)$value->extension_id;
			$value->link = '<a href="'.$link.'">'.JText::_($value->name).'</a>';
			$i++;
		}
		
	
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
	
		$this->state		= $this->get('State');
		
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'phocapdfplugins.php';
		//$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo	= PhocaPDFPluginsHelper::getActions();
		
		JToolBarHelper::title(   JText::_( 'COM_PHOCAPDF_PLUGINS' ), 'pdf' );
		
		$bar = & JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Link', 'back', 'COM_PHOCAPDF_CONTROL_PANEL', 'index.php?option=com_phocapdf' );
		
		if ($canDo->get('core.edit')) {
			JToolBarHelper::apply('phocapdfplugin.apply', 'JTOOLBAR_APPLY');
			//JToolBarHelper::save('phocapdfplugin.save', 'JTOOLBAR_SAVE');
		}
		JToolBarHelper::divider();
		
		JToolBarHelper::help( 'screen.phocapdf', true );
		
	}
}
