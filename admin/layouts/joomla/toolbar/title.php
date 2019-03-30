<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

$icon = empty($displayData['icon']) ? 'generic' : preg_replace('#\.[^ .]*$#', '', $displayData['icon']);
?>
<h1 class="page-title">
	<span class="fa fa-<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php echo JText::_('COM_JVOTER') . ': ' .$displayData['title']; ?>
</h1>
