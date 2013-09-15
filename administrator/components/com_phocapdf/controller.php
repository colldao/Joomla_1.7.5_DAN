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
jimport('joomla.application.component.controller');

// Submenu view
$view	= JRequest::getVar( 'view', '', '', 'string', JREQUEST_ALLOWRAW );

$l['cp']	= 'COM_PHOCAPDF_CONTROL_PANEL';
$l['p']		= 'COM_PHOCAPDF_PLUGINS';
$l['f']		= 'COM_PHOCAPDF_FONTS';
$l['i']		= 'COM_PHOCAPDF_INFO';

if ($view == '' || $view == 'phocapdfcp') {
	JSubMenuHelper::addEntry(JText::_($l['cp']), 'index.php?option=com_phocapdf', true);
	JSubMenuHelper::addEntry(JText::_($l['p']), 'index.php?option=com_phocapdf&view=phocapdfplugins');
	JSubMenuHelper::addEntry(JText::_($l['f']), 'index.php?option=com_phocapdf&view=phocapdffonts' );
	JSubMenuHelper::addEntry(JText::_($l['i']), 'index.php?option=com_phocapdf&view=phocapdfinfo');
}

if ($view == 'phocapdfplugins') {
	JSubMenuHelper::addEntry(JText::_($l['cp']), 'index.php?option=com_phocapdf');
	JSubMenuHelper::addEntry(JText::_($l['p']), 'index.php?option=com_phocapdf&view=phocapdfplugins', true);
	JSubMenuHelper::addEntry(JText::_($l['f']), 'index.php?option=com_phocapdf&view=phocapdffonts' );
	JSubMenuHelper::addEntry(JText::_($l['i']), 'index.php?option=com_phocapdf&view=phocapdfinfo');
}

if ($view == 'phocapdffonts') {
	JSubMenuHelper::addEntry(JText::_($l['cp']), 'index.php?option=com_phocapdf');
	JSubMenuHelper::addEntry(JText::_($l['p']), 'index.php?option=com_phocapdf&view=phocapdfplugins');
	JSubMenuHelper::addEntry(JText::_($l['f']), 'index.php?option=com_phocapdf&view=phocapdffonts', true );
	JSubMenuHelper::addEntry(JText::_($l['i']), 'index.php?option=com_phocapdf&view=phocapdfinfo');
}

if ($view == 'phocapdfinfo') {
	JSubMenuHelper::addEntry(JText::_($l['cp']), 'index.php?option=com_phocapdf');
	JSubMenuHelper::addEntry(JText::_($l['p']), 'index.php?option=com_phocapdf&view=phocapdfplugins');
	JSubMenuHelper::addEntry(JText::_($l['f']), 'index.php?option=com_phocapdf&view=phocapdffonts');
	JSubMenuHelper::addEntry(JText::_($l['i']), 'index.php?option=com_phocapdf&view=phocapdfinfo', true );
}

class PhocaPDFCpController extends JController
{
	function display() {
		parent::display();
	}
}
?>
