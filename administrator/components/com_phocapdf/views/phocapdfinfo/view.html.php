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

class PhocaPDFCpViewPhocaPDFInfo extends JView
{	
	public $tmpl;
	
	function display($tpl = null) {
		
		JHTML::stylesheet( 'administrator/components/com_phocapdf/assets/phocapdf.css' );
		$this->tmpl['version'] = PhocaPDFHelper::getPhocaVersion('com_phocapdf');
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		JToolBarHelper::title( JText::_( 'COM_PHOCAPDF_PDF_INFO' ), 'info.png' );
		JToolBarHelper::cancel( 'cancel', 'COM_PHOCAPDF_CLOSE' );
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.phocapdf', true );
	}
}
?>