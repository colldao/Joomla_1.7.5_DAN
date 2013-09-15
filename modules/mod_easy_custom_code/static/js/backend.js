/**
 * @copyright   Copyright (C) 2009-2013 ThemePartner. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

function tpEccBackend()
{
    var jsFile = document.getElementById('jform_params_tp_js_file');
    if(jsFile)
    {
        jsFile.onchange = function() {
            tpHandleSelectChange(false);
        };
        tpHandleSelectChange(true);
    }
}

function tpHandleSelectChange(first)
{
    var insideHeadOption = document.getElementById('jform_params_tp_js_placement0');
    var aboveOption = document.getElementById('jform_params_tp_js_placement1');
    var belowOption = document.getElementById('jform_params_tp_js_placement2');
    var deferOption = document.getElementById('jform_params_tp_js_placement3');

    if(!tpCreateFile())
    {
        insideHeadOption.disabled = false;
        aboveOption.disabled = false;
        belowOption.disabled = false;
        deferOption.disabled = true;

        if(!first)
        {
            insideHeadOption.checked = true;
            aboveOption.checked = false;
            belowOption.checked = false;
            deferOption.checked = false;
        }
    }
    else
    {
        insideHeadOption.disabled = false;
        aboveOption.disabled = true;
        belowOption.disabled = true;
        deferOption.disabled = false;

        if(!first)
        {
            insideHeadOption.checked = true;
            aboveOption.checked = false;
            belowOption.checked = false;
            deferOption.checked = false;
        }
    }
}

function tpCreateFile()
{
    var fileYes = document.getElementById('jform_params_tp_js_file1');

    if(fileYes.checked)
    {
        return true;
    }

    return false;
}

if (typeof jQuery == 'undefined')
{
    window.onload = tpEccBackend;
}
else
{
   jQuery(document).ready(function() {
        tpEccBackend();
   });
}