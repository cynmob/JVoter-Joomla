<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$app       = JFactory::getApplication();
$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'e.ordering');
$saveOrder = ($listOrder == 'e.ordering' && strtolower($listDirn) == 'asc');

if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_jvoter&task=entries.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'entryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_jvoter&view=entries'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
    		<table class="table table-striped" id="entryList">
    			<thead>
					<tr>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo JHtml::_('searchtools.sort', '', 'e.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
						</th>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'e.state', $listDirn, $listOrder); ?>
						</th>
						<th style="min-width:100px" class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'e.title', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_JVOTER_FIELD_TYPE_LABEL', 'e.vote', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'e.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :?>
					<?php $ordering   = ($listOrder == 'e.ordering'); ?>
					<?php $canEdit    = $user->authorise('core.edit', 'com_jvoter.entry.' . $item->id); ?>
					<?php $canCheckin = $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0; ?>
					<?php $canEditOwn = $user->authorise('core.edit.own', 'com_jvoter.entry.' . $item->id) && $item->created_by == $userId; ?>
					<?php $canChange  = $user->authorise('core.edit.state', 'com_jvoter.entry.' . $item->id) && $canCheckin; ?>
					<tr class="row<?php echo $i % 2; ?>" item-id="<?php echo $item->id ?>">
						<td class="order nowrap center hidden-phone">
							<?php $iconClass = ''; ?>
							<?php if (!$canChange) : ?>
								<?php $iconClass = ' inactive'; ?>
							<?php elseif (!$saveOrder) : ?>
								<?php $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED'); ?>
							<?php endif; ?>
							<span class="sortable-handler<?php echo $iconClass; ?>">
								<span class="icon-menu" aria-hidden="true"></span>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" />
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
							<div class="btn-group">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'entries.', $canChange, 'cb'); ?>
								<?php echo JHtml::_('contestadministrator.moderated', $item->moderated, $i, $canChange); ?>
								<?php // Create dropdown items and render the dropdown list. ?>
								<?php if ($canChange) : ?>
									<?php JHtml::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'entries'); ?>
									<?php JHtml::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'entries'); ?>
									<?php echo JHtml::_('actionsdropdown.render', $this->escape($item->title)); ?>
								<?php endif; ?>
							</div>
						</td>
						<td>
							<div class="pull-left break-word">
								<?php if ($item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'entries.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit || $canEditOwn) : ?>
									<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_jvoter&task=entry.edit&id=' . $item->id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
										<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</div>
						</td>
						<td class="small">
							<?php echo $this->escape($item->vote); ?>
						</td>
						<td class="center hidden-phone">
							<span><?php echo (int) $item->id; ?></span>
						</td>
					</tr>	
				<?php endforeach; ?>
				</tbody>
		</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
</form>