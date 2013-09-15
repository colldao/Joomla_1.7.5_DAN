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
jimport( 'joomla.application.component.view' );
jimport('joomla.client.helper');
jimport('joomla.filesystem.file');

class PhocaPDFCpViewPhocaPDFFonts extends JView
{
	protected $ftp;
	protected $state;
	protected $items;
	protected $pagination;
	
	function display($tpl = null) {
		
		JHTML::stylesheet( 'administrator/components/com_phocapdf/assets/phocapdf.css' );
		$this->ftp			= JClientHelper::setCredentialsFromRequest('ftp');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
	
		$this->state		= $this->get('State');
		
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'phocapdffonts.php';
		$canDo	= PhocaPDFFontsHelper::getActions();
		
		JToolBarHelper::title(   JText::_( 'COM_PHOCAPDF_FONTS' ), 'font' );
		
		$bar = & JToolBar::getInstance( 'toolbar' );
		$bar->appendButton( 'Link', 'back', 'COM_PHOCAPDF_CONTROL_PANEL', 'index.php?option=com_phocapdf' );
		
		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList( JText::_( 'COM_PHOCAPDF_WARNING_DELETE_ITEMS' ), 'phocapdffont.delete', 'COM_PHOCAPDF_DELETE');
		}
		JToolBarHelper::divider();
		
		JToolBarHelper::help( 'screen.phocapdf', true );
		
	}
}	
?>
