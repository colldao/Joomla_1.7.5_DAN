<?php
/**
 * @copyright   Copyright (C) 2009-2013 ThemePartner. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

class JFormFieldBootstrap extends JFormField
{
	protected $type = 'Bootstrap';

	protected function getInput()
	{
		// include backend css
        JHTML::stylesheet(JURI::root() . 'modules/mod_easy_custom_code/static/css/backend.css');
        JHTML::script(JURI::root() . 'modules/mod_easy_custom_code/static/js/backend.js');
	}

    protected function getLabel()
    {
        return '';
    }
}