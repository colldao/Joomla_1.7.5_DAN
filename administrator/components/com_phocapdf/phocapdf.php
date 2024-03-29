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
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'phocapdf.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'phocapdfparams.php' );
jimport('joomla.application.component.controller');
$controller	= JController::getInstance('PhocaPDFCp');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

?>