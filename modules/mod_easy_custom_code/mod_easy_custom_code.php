<?php
/**
 * @copyright   Copyright (C) 2009-2013 ThemePartner. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper/CssModuleStaticContentHelper.php';
require_once dirname(__FILE__) . '/helper/JsModuleStaticContentHelper.php';

$app = JFactory::getApplication();
$document = JFactory::getDocument();
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$customHtml = $params->get('tp_html');
$customCss = $params->get('tp_css');
$customJs = $params->get('tp_js');
$tpJsFile = $params->get('tp_js_file');
$jsPlacement = (int)$params->get('tp_js_placement');

if(!empty($customCss))
{
    $cssHelper = new CssModuleStaticContentHelper('mod_easy_custom_code', $module->id);

    if($params->get('tp_css_file'))
    {
        $cssHelper->writeFile($customCss);
        if($cssHelper->isFileCreated())
        {
            $cssHelper->addToDocument($document);
        }
        else
        {
            $document->addStyleDeclaration($customCss);
        }
    }
    else
    {
        $document->addStyleDeclaration($customCss);
    }
}

if(!empty($customJs))
{
    $jsHelper = new JsModuleStaticContentHelper('mod_easy_custom_code', $module->id);

    if($tpJsFile)
    {
        $jsHelper->writeFile($customJs);
        if($jsHelper->isFileCreated())
        {
            if($jsPlacement != 3)
                $jsHelper->addToDocument($document);
        }
        else
        {
            $document->addScriptDeclaration($customJs);
        }
    }
    else
    {
        if($jsPlacement == 0)
        {
            $document->addScriptDeclaration($customJs);
        }
    }
}

require JModuleHelper::getLayoutPath('mod_easy_custom_code', $params->get('layout', 'default'));