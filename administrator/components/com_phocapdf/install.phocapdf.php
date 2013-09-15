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
jimport( 'joomla.filesystem.folder' );

function com_install() {
	
	$warningStyle 	= 'color: #D35061;-moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px;background: #F1CAD0;padding: 10px; border: 3px solid #D35061';
	$headStyle		= 'font-weight: bold; text-decoration: underline;';
	//$buttonStyle	= 'background-color: #6699cc; margin: 10px; padding:10px 25px 10px 25px; color: #fff; font-weight: bold; -moz-border-radius: 8px; -webkit-border-radius: 8px; border-radius: 8px; border-bottom: 2px solid #215485; border-right: 2px solid #215485; border-top: 2px solid #8fbbe6; border-left: 2px solid #8fbbe6;font-size: x-large;';
	
	$styleInstall = 
	'background: 		url(\''.JURI::base(true).'/components/com_phocapdf/assets/images/btn.png\') repeat-x, url(\''.JURI::base(true).'/components/com_phocapdf/assets/images/bg-install.png\') 10px center no-repeat;
	display: 			inline-block; 
	padding:			15px 30px 15px 50px;
	font-size: 			23px;   
	text-decoration: 	none;
	box-shadow: 		0 1px 2px rgba(0,0,0,0.6);
	-moz-box-shadow: 	0 1px 2px rgba(0,0,0,0.6);
	-webkit-box-shadow: 0 1px 2px rgba(0,0,0,0.6);
	border-radius: 			5px;
	-moz-border-radius: 	5px; 
	-webkit-border-radius: 	5px;
	border-bottom: 		1px solid rgba(0,0,0,0.25);
	position: 			relative;
	cursor: 			pointer;
	text-shadow: 		0 -1px 1px rgba(0,0,0,0.25);
	font-weight: 		bold;
	color: 				#fff;
	background-color: 	#6699cc;';
	
	$styleUpgrade = 
	'background: 		url(\''.JURI::base(true).'/components/com_phocapdf/assets/images/btn.png\') repeat-x, url(\''.JURI::base(true).'/components/com_phocapdf/assets/images/bg-upgrade.png\') 10px center no-repeat;
	display: 			inline-block; 
	padding:			15px 30px 15px 50px;
	font-size: 			23px; 
	text-decoration: 	none;
	box-shadow: 		0 1px 2px rgba(0,0,0,0.6);
	-moz-box-shadow: 	0 1px 2px rgba(0,0,0,0.6);
	-webkit-box-shadow: 0 1px 2px rgba(0,0,0,0.6);
	border-radius: 			5px;
	-moz-border-radius: 	5px; 
	-webkit-border-radius: 	5px;
	border-bottom: 		1px solid rgba(0,0,0,0.25);
	position: 			relative;
	cursor: 			pointer;
	text-shadow: 		0 -1px 1px rgba(0,0,0,0.25);
	font-weight: 		bold;
	color: 				#fff;
	background-color: 	#6699cc;';

	$lang = JFactory::getLanguage();
	$lang->load('com_phocapdf.sys');
	$lang->load('com_phocapdf');

	?>
	<div style="padding:15px;background:#fff;color: #777;font-size:105%;">
		<a style="text-decoration:none" href="http://www.phoca.cz/" target="_blank"><?php
			echo  JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/logo.png', 'Phoca.cz');
		?></a>
		<div style="position:relative;float:right;">
			<?php echo  JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/logo-phoca.png', 'Phoca.cz');?>
		</div>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<?php
		$fAv 	= 'com_content/views/article/view.pdf.php';
		$fAd 	= 'com_content/views/article/tmpl/default.php';
		$fCb 	= 'com_content/views/category/tmpl/blog_item.php';
		$fFd 	= 'com_content/views/featured/tmpl/default_item.php';
		$fPh	= 'administrator/components/com_phocapdf/files/';
		
		echo '<p>&nbsp;</p>';
		echo '<p>&nbsp;</p>';
		echo '<p>'. JText::_('COM_PHOCAPDF_INSTALL_SELECT_METHOD') . '</p>';
		echo '<p>&nbsp;</p>';
		
		// Full Install
		echo '<div style="border-top: 1px solid #ccc; margin-bottom:10px"></div>';
		echo '<h2 style="'.$headStyle.'">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_FULL_INSTALL_LABEL').'</h2>';
		echo '<div>'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_FULL_INSTALL_DESC').':</div>';
		echo '<ul>';
		echo ' <li>'. JText::_('COM_PHOCAPDF_INSTALL_INSTALL_COMPONENT').'</li>';
		echo ' <li>'. JText::_('COM_PHOCAPDF_INSTALL_BACKUP_CONTENT').':';
		
		echo '  <ul>';
		echo '   <li><b>components/'.$fAd.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fAd.'.bak</b></li>';
			
		echo '   <li><b>components/'.$fCb.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fCb.'.bak</b></li>';
		
		echo '   <li><b>components/'.$fFd.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fFd.'.bak</b></li>';
		echo '  </ul>';
		echo ' </li>';
		
		echo ' <li>'. JText::_('COM_PHOCAPDF_INSTALL_COPY_FILES_CONTENT_DOC').':';
		echo '  <ul>';
		
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.'pdf/pdf.php</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>libraries/joomla/document/pdf/pdf.php</b></li>';
			
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.$fAv.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fAv.'</b></li>';
	
		
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.$fAd.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fAd.'</b></li>';
			
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.$fCb.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fCb.'</b></li>';
		
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.$fFd.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fFd.'</b></li>';
		echo '  </ul>';
		echo ' </li>';
		echo '</ul>';
		
		echo '<div style="'.$warningStyle.'">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_FULL_INSTALL_WARNING') . '</div>';
		echo '<p>&nbsp;</p>';
		echo '<div style="text-align: right;margin: 10px;"><span style="'.$styleInstall.'">'
			.'<a style="color:#fff" href="index.php?option=com_phocapdf&amp;task=phocapdfinstall.fullinstall">'
			.JText::_('COM_PHOCAPDF_INSTALL_METHOD_FULL_INSTALL_LABEL').'</a></span></div>';
		echo '<p>&nbsp;</p>';
		
		// Custom Install
		echo '<div style="border-top: 1px solid #ccc; margin-bottom:10px"></div>';
		echo '<h2 style="'.$headStyle.'">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_CUSTOM_INSTALL_LABEL').'</h2>';
		echo '<div>'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_CUSTOM_INSTALL_DESC').'</div>';
		
		echo '<ul>';
		echo ' <li>'. JText::_('COM_PHOCAPDF_INSTALL_INSTALL_COMPONENT').'</li>';
		echo ' <li>'. JText::_('COM_PHOCAPDF_INSTALL_COPY_FILES_DOC').':';
		echo '  <ul>';
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.'pdf/pdf.php</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>libraries/joomla/document/pdf/pdf.php</b></li>';
		echo '   <li>'.JText::_('COM_PHOCAPDF_FROM').' <b>'.$fPh.$fAv.'</b> '.JText::_('COM_PHOCAPDF_TO')
			.' <b>components/'.$fAv.'</b></li>';
		echo '  </ul>';
		echo ' </li>';
		echo '</ul>';
		
		echo '<div style="'.$warningStyle.'">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_CUSTOM_INSTALL_WARNING') . '</div>';
		echo '<p>&nbsp;</p>';
		echo '<div style="text-align: right;margin: 10px;"><span style="'.$styleInstall.'">'
			.'<a style="color:#fff" href="index.php?option=com_phocapdf&amp;task=phocapdfinstall.custominstall">'
			.JText::_('COM_PHOCAPDF_INSTALL_METHOD_CUSTOM_INSTALL_LABEL').'</a></span></div>';
		echo '<p>&nbsp;</p>';
		
		// Upgrade
		echo '<div style="border-top: 1px solid #ccc; margin-bottom:10px"></div>';
		echo '<h2 style="'.$headStyle.'">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_UPGRADE_LABEL').'</h2>';
		echo '<div>'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_UPGRADE_DESC').'</div>';
		//echo '<div class="warning">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_UPGRADE_WARNING') . '</div>';
		echo '<p>&nbsp;</p>';
		echo '<div style="text-align: right;margin: 10px;"><span style="'.$styleUpgrade.'">'
			.'<a style="color:#fff" href="index.php?option=com_phocapdf&amp;task=phocapdfinstall.upgrade">'
			.JText::_('COM_PHOCAPDF_INSTALL_METHOD_UPGRADE_LABEL').'</a></span></div>';
		echo '<p>&nbsp;</p>';
		
		echo '<div style="border-top: 1px solid #ccc; margin-bottom:10px"></div>';
		
		echo '<div style="'.$warningStyle.'">'. JText::_('COM_PHOCAPDF_INSTALL_METHOD_ALL_WARNING') . '</div>';
		
		
		echo '<p>&nbsp;</p>'; ?>

		
		<p style="color: #c0c0c0;">
		<a href="http://www.phoca.cz/phocapdf/" target="_blank">Phoca PDF Main Site</a><br />
		<a href="http://www.phoca.cz/documentation/" target="_blank">Phoca PDF User Manual</a><br />
		<a href="http://www.phoca.cz/forum/" target="_blank">Phoca PDF Forum</a><br />
		<a href="http://www.phoca.cz/" target="_blank">Phoca.cz</a>
		</p>
		
		<div style="margin-top:30px;height:39px;background: url('<?php echo JURI::base(true); ?>/components/com_phocapdf/assets/images/line.png') 100% 0 no-repeat;">&nbsp;</div>
		</div>		
<?php
}
?>