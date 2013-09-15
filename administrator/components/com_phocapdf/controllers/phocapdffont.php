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
jimport('joomla.client.helper');
jimport('joomla.application.component.controlleradmin');

class PhocaPDFCpControllerPhocaPDFFont extends JControllerAdmin
{	
	protected	$option 		= 'com_phocapdf';
	
	public function &getModel($name = 'PhocaPDFFonts', $prefix = 'PhocaPDFCpModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	
	function delete() {

		$cid 	= JRequest::getVar( 'cid', array(), '', 'array' );// POST (Icon), GET (Small Icon)		
		JArrayHelper::toInteger($cid);
	
		if (count($cid ) < 1) {
			JError::raiseError(500, JText::_( 'COM_PHOCAPDF_SELECT_ITEM_DELETE' ) );
		}
		
		$model 		= $this->getModel();
		$errorMsg	= $model->delete($cid);
 		if($errorMsg != '') {
			//echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
			$msg = JText::_( 'COM_PHOCAPDF_FONT_ERROR_DELETE' ) . '<br />' . $errorMsg;
		} else {
			$msg = JText::_( 'COM_PHOCAPDF_FONT_SUCCESS_DELETE' );
		}

		$this->setRedirect( 'index.php?option=com_phocapdf&view=phocapdffonts', $msg );
	}
	
	function install() {
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$post 	= JRequest::get('post');
		$ftp 	= JClientHelper::setCredentialsFromRequest('ftp');

		$model = $this->getModel();

		if ($model->install()) {
			$cache = &JFactory::getCache('mod_menu');
			$cache->clean();
			$msg = JText::_('COM_PHOCAPDF_NEW_FONT_INSTALLED');
		}
		
		$this->setRedirect( 'index.php?option=com_phocapdf&view=phocapdffonts', $msg );
	}
}
?>
