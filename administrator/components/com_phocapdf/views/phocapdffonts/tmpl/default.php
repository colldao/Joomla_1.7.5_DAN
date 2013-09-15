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
 
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
$user		= JFactory::getUser();
$userId		= $user->get('id');
?>
<script language="javascript" type="text/javascript">
function submitbuttonupload(pressbutton) {
	document.uploadForm.submit();
}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_phocapdf&view=phocapdffonts'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="editcell">
		<table class="adminlist">
			<thead>
			<tr>
				<th width="5"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" /></th>
				<th class="title" width="93%"><?php echo JText::_('COM_PHOCAPDF_FONT_NAME'); ?></th>
				<th class="title" width="5%"><?php echo JText::_('COM_PHOCAPDF_DELETE'); ?></th>
			</tr>
			</thead>
			<tbody><?php
				
if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {


$canDelete		= $user->authorise('core.delete', 'com_phocamenu');
$linkRemove 	= 'javascript:void(0);';
$onClickRemove 	= 'javascript:if (confirm(\''.JText::_('COM_PHOCAPDF_WARNING_DELETE_ITEMS').'\')){'
				 .' return listItemTask(\'cb'. $i .'\',\'phocapdffont.delete\');'
				 .'}';
				
echo '<tr class="row'. $i % 2 .'">';
echo '<td class="center">'. JHtml::_('grid.id', $i, $item->id) . '</td>';
echo '<td>';
echo $this->escape($item->name);
echo '</td>';
echo '<td align="center">';
if ($canDelete) {	
echo '<a href="'. $linkRemove.'" onclick="'.$onClickRemove.'" title="'. JText::_('COM_PHOCAPDF_DELETE').'">'
	. JHTML::_('image', 'administrator/components/com_phocapdf/assets/images/icon-16-trash.png', JText::_('COM_PHOCAPDF_DELETE') )
	.'</a>';
}
echo '</td>';
		}
	}
echo '</tbody>'."\n";		
echo '<tfoot><tr><td colspan="3">'. $this->pagination->getListFooter().'</td></tr></tfoot>'."\n";
echo '</table>' . "\n" . '</div>'."\n";
	
?>
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_phocapdf" />
<input type="hidden" name="liststart" value="<?php echo $this->state->get('list.start'); ?>" />
<input type="hidden" name="listlimit" value="<?php echo $this->state->get('list.limit'); ?>" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::_('form.token'); ?>
</form>

<form enctype="multipart/form-data" action="index.php" method="post" name="uploadForm">
<?php
if ($this->ftp) {
	PhocaPDFHelper::renderFTPaccess();
} ?>

<table class="adminform" border="0">
<tr>
	<td>&nbsp;</td>
	<td colspan="2"><b><?php echo JText::_( 'COM_PHOCAPDF_UPLOAD_PHOCAPDF_FONT_INSTALL_FILE' ); ?></b></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td width="120"><label for="install_package"><?php echo JText::_( 'COM_PHOCAPDF_PACKAGE_FILE' ); ?>:</label></td>
	<td>
		<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
		<input class="button" type="button" value="<?php echo JText::_( 'COM_PHOCAPDF_UPLOAD_FILE' ); ?> &amp; <?php echo JText::_( 'COM_PHOCAPDF_INSTALL' ); ?>" onclick="submitbuttonupload()" />
	</td>
</tr>
<tr>
	<td colspan="3">&nbsp;</td>
</tr>
</table>

<div id="ppdf-updatef"><a href="http://www.phoca.cz/phocapdf-fonts" target="_blank"><?php echo JText::_('COM_PHOCAPDF_NEW_FONT_DOWNLOAD'); ?></a></div>
	
<input type="hidden" name="type" value="" />
<input type="hidden" name="task" value="phocapdffont.install" />
<input type="hidden" name="option" value="com_phocapdf" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>