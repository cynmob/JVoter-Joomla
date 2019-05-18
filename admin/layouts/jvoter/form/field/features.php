<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');
$features = $displayData;
?>
<?php if (empty($features)) : ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php else : ?>
<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th width="1%" class="center"><?php echo JHtml::_('grid.checkall');?></th>
			<th style="min-width:150px" class="nowrap"><?php echo JText::_('JGLOBAL_TITLE');?></th>
			<th><?php echo JText::_('COM_JVOTER_FIELD_LABEL_LABEL');?></th>
			<th><?php echo JText::_('COM_JVOTER_FIELD_VALUE_LABEL');?></th>		
			<th><?php echo JText::_('JGLOBAL_DESCRIPTION');?></th>
		</tr>
	</thead>
	<tbody>
	<?php  foreach($features as $i => $feature):?>
		<tr class="row<?php echo $i % 2;?>">
			<td class="center"><?php echo JHtml::_('grid.id', $i, $feature->id);?></td>
			<td>
				<div class="pull-left break-word">
				<?php echo $this->escape($feature->title);?>				
				</div>
			</td>
			<td><input type="text" value="<?php echo $feature->translate ? JText::_($feature->label) : $feature->label;?>"/></td>
			<td>
			<?php 
			switch($feature->type)
			{
			    case 'boolean':
			        
			        $options = array(
			             JHtml::_('select.option', '1', JText::_('JYES')),
			             JHtml::_('select.option', '0', JText::_('JNO'))
			        );
			        
			        $displayData = array(
			            'autocomplete'   => false,
			            'autofocus'      => false,
			            'class'          => 'btn-group btn-group-yesno',
			            'disabled'       => false,
			            'id'             => $feature->namekey,
			            'name'           => $feature->namekey,
			            'onchange'       => '',
			            'onclick'        => '',
			            'readonly'       => false,
			            'required'       => false,
			            'value'          => empty($feature->value) ? '0' : $feature->value,
			            'options'        => $options
			        );
			        
			        $html = JLayoutHelper::render('joomla.form.field.radio', $displayData);
			        
			        break;
			        
			    case 'integer':
			        $html = '<input type="number" name="'.$feature->namekey.'" value="'.$feature->value.'" />';
			        break;
			    case 'text':
			    default:
			        $html = '<input type="text" name="'.$feature->namekey.'" value="'.$feature->value.'" />';
			        break;
			}
			
			echo $html;
			?>			
			</td>
			<td><div class="small"><?php echo $feature->description;?></div></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
<?php endif;?>