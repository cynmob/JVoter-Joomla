<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "plan.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
');

?>

<form action="<?php echo JRoute::_('index.php?option=com_jvoter&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'features')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'features', JText::_('COM_JVOTER_VIEW_FIELD_FIELDSET_FEATURES', true)); ?>
		<div class="row-fluid">
			<div class="span9">              
				<fieldset class="adminform">
					<?php echo $this->form->getInput('features'); ?>
				</fieldset>	
				<div class="alert alert-info">
                	<h4 class="alert-heading"><?php echo JText::_('COM_JVOTER_COMMON_NOTE');?></h4>
                 	<div class="alert-message"><?php echo JText::_('COM_JVOTER_FEATURE_LIST_NOTE');?></div>
                </div>			
			</div>
			<div class="span3">
				<fieldset class="form-vertical">
					<?php echo $this->form->renderField('price'); ?>
				</fieldset>
				<?php $this->set('fields',
						array(
							array(
								'published',
								'state',
								'enabled',
							)	
						)
				); ?>
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				<?php $this->set('fields', null); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>	
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'description', JText::_('JGLOBAL_DESCRIPTION', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span9">
				<fieldset class="adminform">
					<?php echo $this->form->getInput('description'); ?>
				</fieldset>
			</div>
			<div class="span3">
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_JVOTER_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>		
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>		
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
