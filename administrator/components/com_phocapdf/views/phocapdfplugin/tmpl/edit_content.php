<?php defined('_JEXEC') or die('Restricted access'); 


echo '<div id="phocapdf-pane">';
//$pane =& JPane::getInstance('Tabs', array('startOffset'=> 0));
echo JHtml::_('tabs.start', 'config-tabs-com_phocapdf-plugin', array('useCookie'=>1));
//echo $pane->startPane( 'pane' );

// - - - - - - - - - - - - - - - 
// Site
//echo $pane->startPanel( JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-site.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_SITE'), 'site' );

echo JHtml::_('tabs.panel', JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-site.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_SITE'), 'site');
echo '<div style="font-size:1px;height:1px;margin:0px;padding:0px;">&nbsp;</div>';//because of IE bug
if($output = PhocaPDFHelperParams::renderSite( $this->form->getFieldset('phocasite'))) {
	echo $output;
} else {
	echo '<div style="text-align: center; padding: 5px;">'.JText::_('COM_PHOCAPDF_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM').'</div>';
}
//echo $pane->endPanel();
// - - - - - - - - - - - - - - -

// - - - - - - - - - - - - - - - 
// Header
//echo $pane->startPanel( JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-header.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_HEADER'), 'header' );
echo JHtml::_('tabs.panel', JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-header.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_HEADER'), 'header');
echo '<div style="font-size:1px;height:1px;margin:0px;padding:0px;">&nbsp;</div>';//because of IE bug
if($output = PhocaPDFHelperParams::renderMisc($this->form->getFieldset('phocaheader'))) {
	echo $output;
} else {
	echo '<div style="text-align: center; padding: 5px; ">'.JText::_('COM_PHOCAPDF_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM').'</div>';
}
//echo $pane->endPanel();
// - - - - - - - - - - - - - - -

// - - - - - - - - - - - - - - - 
// Footer
//echo $pane->startPanel( JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-footer.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_FOOTER'), 'footer' );
echo JHtml::_('tabs.panel', JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-footer.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_FOOTER'), 'footer');
echo '<div style="font-size:1px;height:1px;margin:0px;padding:0px;">&nbsp;</div>';//because of IE bug
if($output = PhocaPDFHelperParams::renderMisc($this->form->getFieldset('phocafooter'))) {
	echo $output;
} else {
	echo '<div style="text-align: center; padding: 5px; ">'.JText::_('COM_PHOCAPDF_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM').'</div>';
}
//echo $pane->endPanel();
// - - - - - - - - - - - - - - -

// - - - - - - - - - - - - - - - 
// PDF
//echo $pane->startPanel( JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-pdf.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_PDF'), 'pdf' );
echo JHtml::_('tabs.panel', JHTML::_( 'image', 'administrator/components/com_phocapdf/assets/images/icon-16-pdf.png','') . '&nbsp;'.JText::_('COM_PHOCAPDF_PDF'), 'pdf');
echo '<div style="font-size:1px;height:1px;margin:0px;padding:0px;">&nbsp;</div>';//because of IE bug
if($output = PhocaPDFHelperParams::renderMisc($this->form->getFieldset('phocapdf'))) {
	echo $output;
} else {
	echo '<div style="text-align: center; padding: 5px;">'.JText::_('COM_PHOCAPDF_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM').'</div>';
}
//echo $pane->endPanel();
// - - - - - - - - - - - - - - -

//echo $pane->endPane();
echo JHtml::_('tabs.end');
echo '</div>';

echo '<div id="phocapdf-apply"><a href="#" onclick="javascript: submitbutton(\'phocapdfplugin.apply\')">'.JText::_('COM_PHOCAPDF_SAVE').'</a></div>';
	
echo '<div style="margin-top:20px">';	
if (isset($this->item->enabled)) {
	if ($this->item->enabled == 1) {
		echo JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/icon-16-true.png', '' )
		.' ' . JText::_('COM_PHOCAPDF_PLUGIN_IS_ENABLED_IN_MANAGER');
	} else {
		echo JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/icon-16-false.png', '' )
		.' '. JText::_('COM_PHOCAPDF_PLUGIN_IS_DISABLED_IN_MANAGER');
	}
	
}

echo '<br />' .JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/icon-16-warning.png', '' )
.' '. JText::_('COM_PHOCAPDF_SETTINGS_WARNING');

echo '</div>';
echo '<div style="clear:both"></div>';