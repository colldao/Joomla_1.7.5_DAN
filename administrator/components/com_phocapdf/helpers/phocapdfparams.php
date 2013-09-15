<?php
/*
 * @package Joomla 1.5
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */ 
defined('_JEXEC') or die();
class PhocaPDFHelperParams
{
	function renderSite($fieldsets) {


		$html = array ();

		$e = count($fieldsets) - 1;// count of items which don't begin with 1 but with 0
		$i = 0;
		foreach ($fieldsets as $field) {
		
			switch($field->id) {
				case 'jform_params_margin_top':
				
					if ($field->label) {
						$html[] = '<table><tr><td></td><td align="center"><div class="imglbl">'. $field->label.$field->input. '</div></td><td></td></tr>';
					} else {
						$html[] = '<table><tr><td></td><td align="center"><div class="imglbl">'.$field->input. '</div></td><td></td></tr>';
					}
				break;
				
				case 'jform_params_margin_left':
					$img = JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/params-site.png', '');
				
					if ($field->label) {
						$html[] = '<tr><td align="center"><div class="imglbl">'.$field->label.$field->input . '</div></td><td align="center">'.$img.'</td>';
					} else {
						$html[] = '<tr><td><div class="imglbl">'.$field->input . '</div></td><td align="center">'.$img.'</td>';
					}
				break;
				
				case 'jform_params_margin_right':
				
					if ($field->label) {
						$html[] = '<td align="center"><div class="imglbl">'.$field->label.$field->input . '</div></td></tr>';
					} else {
						$html[] = '<td><div class="imglbl">'.$field->input . '</div></td></tr>';
					}
				break;
				
				case 'jform_params_margin_bottom':
					if ($field->label) {
						$html[] = '<tr><td></td><td align="center"><div class="imglbl">'.$field->label.$field->input.'</div></td><td></td></tr></table>'
								 .'<div class="phocapdf-hr"></div>';
					} else {
						$html[] = '<tr><td></td><td align="center"><div class="imglbl">'.$field->input . '</div></td><td></td></tr></table>'
								 .'<div class="phocapdf-hr"></div>';
					}
				break;
				
				default:
					if ($i == 4) {//margin-top, margin-bottom, margin-left, margin-right
						$html[] = '<div class="clr"></div>';
					}
					if ($field->label) {
						$html[] = ''.$field->label.$field->input . '';
					} else {
						$html[] = ''.$field->input . '';
					}
					if ($i == $e) {
						$html[] = '<div class="clr"></div>';
					}
					
				break;
				
				
			}
			$i++;
			
		}

		if (count($fieldsets) < 1) {
			$html[] = '<div>'.JText::_('COM_PHOCAPDF_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM').'</div>';
		}

		

		return implode("\n", $html);
	}
	
	
	function renderMisc($fieldsets) {
		
		
		$html = array ();
		
		$e = count($fieldsets) - 1;// count of items which don't begin with 1 but with 0
		$i = 0;
		foreach ($fieldsets as $field) {
	
			if ($i == 0) {
				$html[] = '<table>';
			}
			switch($field->id) {
				case 'jform_params_header_margin':
					$img = JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/params-header.png', '');
					if ($field->label) {
						$html[] = '<tr><td>'.$field->label.$field->input 
						. '<div style="position:relative;float:right;margin-left:20px;">'.$img.'</div></td></tr>';
					} else {
						$html[] = '<tr><td>'.$field->input 
						. '<div style="position:relative;float:right;margin-left:20px;">'.$img.'</div></td></tr>';
					}
				break;
				
				case 'jform_params_footer_margin':
					$img = JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/params-footer.png', '');
					if ($field->label) {
						$html[] = '<tr><td>'.$field->label.$field->input 
						. '<div style="position:relative;float:right;margin-left:20px;">'.$img.'</div></td></tr>';
					} else {
						$html[] = '<tr><td>'.$field->input 
						. '<div style="position:relative;float:right;margin-left:20px;">'.$img.'</div></td></tr>';
					}
				break;
			
				default:
			
				if ($field->label) {
					$html[] = '<tr><td>'.$field->label.$field->input . '</td></tr>';
				} else {
					$html[] = '<tr><td></td><td>'.$field->input . '</td></tr>';
				}
				break;
			}
			

			if ($i == $e) {
				$html[] = '</table>';
			}
			$i++;
		}
		
		return implode("\n", $html);
	}
}
?>