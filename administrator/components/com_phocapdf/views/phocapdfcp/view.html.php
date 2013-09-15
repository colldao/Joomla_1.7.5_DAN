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
jimport('joomla.html.pane');

class PhocaPDFCpViewPhocaPDFCp extends JView
{
	public $tmpl;
	public function display($tpl = null) {
		
		JHtml::stylesheet( 'administrator/components/com_phocapdf/assets/phocapdf.css' );
		$this->tmpl['version'] = PhocaPDFHelper::getPhocaVersion('com_phocapdf');
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		require_once JPATH_COMPONENT.DS.'helpers'.DS.'phocapdfcp.php';

		$state	= $this->get('State');
		$canDo	= PhocaPDFCpHelper::getActions();
		JToolBarHelper::title( JText::_( 'COM_PHOCAPDF_PDF_CONTROL_PANEL' ), 'phoca.png' );
		
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_phocapdf');
			JToolBarHelper::divider();
		}
		
		JToolBarHelper::help( 'screen.phocapdf', true );
	}
}
?>