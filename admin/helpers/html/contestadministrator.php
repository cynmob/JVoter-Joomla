<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

JLoader::register('JVoterHelper', JPATH_ADMINISTRATOR . '/components/com_jvoter/helpers/jvoter.php');

/**
 * Content HTML helper
 *
 */
abstract class JHtmlContestAdministrator
{	
	/**
	 * Show the feature/unfeature links
	 *
	 * @param   integer  $value      The state value
	 * @param   integer  $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public static function featured($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array('unfeatured', 'contests.featured', 'COM_JVOTER_UNFEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
			1 => array('featured', 'contests.unfeatured', 'COM_JVOTER_FEATURED', 'JGLOBAL_TOGGLE_FEATURED'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3])
				. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. JHtml::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
	
	/**
	 * Show the approve/disapprove links
	 *
	 * @param   integer  $value      The state value
	 * @param   integer  $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 */
	public static function moderated($value = 0, $i, $canChange = true)
	{
	    JHtml::_('bootstrap.tooltip');
	    
	    // Array of image, task, title, action
	    $states = array(
	        0 => array('thumbs-down', 'contests.approved', 'COM_JVOTER_DISAPPROVED', 'COM_JDONATE_TOGGLE_MODERATED'),
	        1 => array('thumbs-up', 'contests.disapproved', 'COM_JVOTER_APPROVED', 'COM_JDONATE_TOGGLE_MODERATED'),
	    );
	    $state = ArrayHelper::getValue($states, (int) $value, $states[1]);
	    $icon  = $state[0];
	    
	    if ($canChange)
	    {
	        $html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
	            . ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3])
	            . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
	    }
	    else
	    {
	        $html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
	            . JHtml::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
	    }
	    
	    return $html;
	}
}
