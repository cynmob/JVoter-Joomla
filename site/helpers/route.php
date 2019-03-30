<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * JVoter Component Route Helper.
 *
 * @since 1.5
 */
abstract class JVoterHelperRoute
{

	/**
	 * Get the contest route.
	 *
	 * @param integer $id
	 *        	The route of the contest item.
	 * @param integer $catid
	 *        	The category ID.
	 * @param integer $language
	 *        	The language code.
	 * @param string $layout
	 *        	The layout value.
	 *        	
	 * @return string The contest route.
	 *        
	 * @since 1.5
	 */
	public static function getContestRoute ($id, $catid = 0, $layout = null)
	{
		// Create the link
		$link = 'index.php?option=com_jvoter&view=contest&id=' . $id;
		
		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}
		
		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}
		
		return $link;
	}

	/**
	 * Get the category route.
	 *
	 * @param integer $catid
	 *        	The category ID.
	 * @param integer $language
	 *        	The language code.
	 * @param string $layout
	 *        	The layout value.
	 *        	
	 * @return string The article route.
	 *        
	 * @since 1.5
	 */
	public static function getCategoryRoute ($catid, $language = 0, $layout = null)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}
		
		if ($id < 1)
		{
			return '';
		}
		
		$link = 'index.php?option=com_jvoter&view=category&id=' . $id;
		
		if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
		}
		
		if ($layout)
		{
			$link .= '&layout=' . $layout;
		}
		
		return $link;
	}

	/**
	 * Get the form route.
	 *
	 * @param integer $id
	 *        	The form ID.
	 *        	
	 * @return string The article route.
	 *        
	 * @since 1.5
	 */
	public static function getFormRoute ($id)
	{
		return 'index.php?option=com_jvoter&task=contest.edit&a_id=' . (int) $id;
	}
}
