<?php defined('_JEXEC') or die('Restricted access');?>

<form action="index.php" method="post" name="adminForm">
<div class="adminform phoca-adminform-color">
<div class="cpanel-left">
	<div id="cpanel">
		<?php
		$component = 'com_phocapdf';
		$link = 'index.php?option='.$component.'&view=phocapdfplugins';
		echo PhocaPDFControlPanel::quickIconButton( $component, $link, 'icon-48-pdf.png', JText::_( 'COM_PHOCAPDF_PLUGINS' ) );

		$link = 'index.php?option='.$component.'&view=phocapdffonts';
		echo PhocaPDFControlPanel::quickIconButton( $component, $link, 'icon-48-font.png', JText::_( 'COM_PHOCAPDF_FONTS' ) );

		$link = 'index.php?option='.$component.'&view=phocapdfinfo';
		echo PhocaPDFControlPanel::quickIconButton( $component, $link, 'icon-48-info.png', JText::_( 'COM_PHOCAPDF_INFO' ) );
		
		
		?>
			
		<div style="clear:both">&nbsp;</div>
		<p>&nbsp;</p>
		<div style="text-align:center;padding:0;margin:0;border:0">
			<iframe style="padding:0;margin:0;border:0" src="http://www.phoca.cz/adv/phocapdf" noresize="noresize" frameborder="0" border="0" cellspacing="0" scrolling="no" width="500" marginwidth="0" marginheight="0" height="125">
			<a href="http://www.phoca.cz/adv/phocapdf" target="_blank">Phoca PDF</a>
			</iframe> 
		</div>
	</div>
</div>
		
<div class="cpanel-right">
	<div style="border:1px solid #ccc;background:#fff;margin:15px;padding:15px">
		<div style="float:right;margin:10px;">
			<?php echo JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/logo-phoca.png', 'Phoca.cz' );?>
		</div>
			
		<?php
		echo '<h3>'.  JText::_('COM_PHOCAPDF_VERSION').'</h3>'
		.'<p>'.  $this->tmpl['version'] .'</p>';

		echo '<h3>'.  JText::_('COM_PHOCAPDF_COPYRIGHT').'</h3>'
		.'<p>© 2007 - '.  date("Y"). ' Jan Pavelka</p>'
		.'<p><a href="http://www.phoca.cz/" target="_blank">www.phoca.cz</a></p>';

		echo '<h3>'.  JText::_('COM_PHOCAPDF_LICENSE').'</h3>'
		.'<p><a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a></p>';
		
		
		echo '<h3>'.  JText::_('COM_PHOCAPDF_TRANSLATION').': '. JText::_('COM_PHOCAPDF_TRANSLATION_LANGUAGE_TAG').'</h3>'
        .'<p>© 2007 - '.  date("Y"). ' '. JText::_('COM_PHOCAPDF_TRANSLATER'). '</p>'
        .'<p>'.JText::_('COM_PHOCAPDF_TRANSLATION_SUPPORT_URL').'</p>';
		
		
		echo '<div style="border-top:1px solid #c2c2c2"></div>';
		?>
		<div id="pg-update"><a href="http://www.phoca.cz/version/index.php?phocapdf=<?php echo $this->tmpl['version'] ;?>" target="_blank"><?php echo JText::_('COM_PHOCAPDF_CHECK_FOR_UPDATE'); ?></a></div>
		<div id="pg-update"><a href="http://www.phoca.cz/phocapdf-plugins" target="_blank"><?php echo JText::_('COM_PHOCAPDF_CHECK_FOR_AVAILABLE_PLUGINS'); ?></a></div>
		<div id="pg-update"><a href="http://www.phoca.cz/phocapdf-fonts" target="_blank"><?php echo JText::_('COM_PHOCAPDF_CHECK_FOR_AVAILABLE_FONTS'); ?></a></div>
		
	</div>
</div>

</div>

<input type="hidden" name="option" value="com_phocapdf" />
<input type="hidden" name="view" value="phocapdfcp" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>