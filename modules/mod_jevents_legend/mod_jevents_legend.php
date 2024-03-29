<?php
/**
 * JEvents Component for Joomla 1.5.x
 *
 * @version     $Id: mod_jevents_legend.php 3141 2011-12-29 10:13:17Z geraintedwards $
 * @package     JEvents
 * @subpackage  Module JEvents Calendar
 * @copyright   Copyright (C) 2006-2008 JEvents Project Group
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://joomlacode.org/gf/project/jevents
 */


defined( '_JEXEC' ) or die( 'Restricted access' );

// record what is running - used by the filters
$registry	=& JRegistry::getInstance("jevents");
$registry->setValue("jevents.activeprocess","mod_jevents_legend");
$registry->setValue("jevents.moduleid", $module->id);
$registry->setValue("jevents.moduleparams", $params);

require_once (dirname(__FILE__).DS.'helper.php');

$jevhelper = new modJeventsLegendHelper();

$theme = JEV_CommonFunctions::getJEventsViewName();
$modtheme = $params->get("com_calViewName", $theme);
if ($modtheme==""){
	$modtheme=$theme;
}
$theme=$modtheme;

$viewclass = $jevhelper->getViewClass($theme, 'mod_jevents_legend',$theme.DS."legend", $params);
$modview = new $viewclass($params, $module->id);
$modview->jevlayout = $theme;
echo $modview->displayCalendarLegend();
