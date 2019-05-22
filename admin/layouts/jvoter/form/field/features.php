<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');
$features = $displayData['features'];
$view = $displayData['view'];
$featureCurrent = $view->value->toObject();
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
		<?php 
		$class = $checked = '';
		if(isset($featureCurrent->{$feature->id}))
		{
		    $class = 'info';
		    $checked = 'checked';
		    $feature->label = $featureCurrent->{$feature->id}->label;
		    $feature->value = $featureCurrent->{$feature->id}->value;
		}
		?>
		<tr class="row<?php echo $i % 2;?> <?php echo $class;?>">
			<td class="center">
				<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $feature->id;?>" onclick="Joomla.isChecked(this.checked);" <?php echo $checked;?> />
			</td>
			<td>
				<div class="pull-left break-word">
				<?php echo $this->escape($feature->title);?>				
				</div>
			</td>
			<td>
				<input name="<?php echo $view->name.'['.$feature->id.'][label]'; ?>" type="text" value="<?php echo $feature->label;?>"/>
			</td>
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
			            'id'             => $view->id . '_' . $feature->namekey,
			            'name'           => $view->name.'['.$feature->id.'][value]',
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
			        $html = '<input type="number" name="'.$view->name.'['.$feature->id.'][value]" value="'.$feature->value.'" />';
			        break;
			    case 'text':
			    default:
			        $html = '<input type="text" name="'.$view->name.'['.$feature->id.'][value]" value="'.$feature->value.'" />';
			        break;
			}
			
			echo $html;
			?>			
			</td>
			<td>
    			<div class="small">
    			<?php echo JVoterHelper::truncateString($feature->description, '150', '<span class="moreless badge badge-info">Show more</span>');?>
    			</div>
			</td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
<?php endif;?>