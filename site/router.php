<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class JVoterRouter
 *
 * @since 3.3
 */
class JVoterRouter extends JComponentRouterBase
{

	/**
	 * Build method for URLs
	 * This method is meant to transform the query parameters into a more human
	 * readable form.
	 * It is only executed when SEF mode is switched on.
	 *
	 * @param
	 *        	array &$query An array of URL arguments
	 *        	
	 * @return array The URL arguments to use to assemble the subsequent URL.
	 *        
	 * @since 3.3
	 */
	public function build (&$query)
	{
		$segments = array();
		$view = null;
		
		if (isset($query['task']))
		{
			$taskParts = explode('.', $query['task']);
			$segments[] = implode('/', $taskParts);
			$view = $taskParts[0];
			unset($query['task']);
		}
		
		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			$view = $query['view'];
			
			unset($query['view']);
		}
		
		if (isset($query['id']))
		{
			if ($view !== null)
			{
				if ($view == 'campaign')
				{
					$db = JFactory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('alias')
						->from('#__jdonate_campaigns')
						->where('id=' . (int) $query['id']);
					$db->setQuery($dbQuery);
					$alias = $db->loadResult();
					if (! empty($alias))
					{
						$query['id'] = $query['id'] . '-' . $alias;
					}
				}
				
				$segments[] = $query['id'];
			}
			
			unset($query['id']);
		}
		
		if (isset($query['campaign_id']))
		{
			if ($view !== null)
			{
				$db = JFactory::getDbo();
				$dbQuery = $db->getQuery(true)
					->select('alias')
					->from('#__jdonate_campaigns')
					->where('id=' . (int) $query['campaign_id']);
				$db->setQuery($dbQuery);
				$alias = $db->loadResult();
				if (! empty($alias))
				{
					$query['campaign_id'] = $query['campaign_id'] . '-' . $alias;
				}
				
				$segments[] = $query['campaign_id'];
			}
			
			unset($query['campaign_id']);
		}
		
		return $segments;
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters.
	 * It is only executed when SEF mode is switched on.
	 *
	 * @param
	 *        	array &$segments The segments of the URL to parse.
	 *        	
	 * @return array The URL attributes to be used by the application.
	 *        
	 * @since 3.3
	 */
	public function parse (&$segments)
	{
		$vars = array();
		
		// View is always the first element of the array
		$vars['view'] = array_shift($segments);
		$end = array_pop($segments);
		
		switch ($vars['view'])
		{
			case 'item':
				
				if (count($segments))
				{
					$vars['campaign_id'] = $end;
					$vars['id'] = array_pop($segments);
				}
				else
				{
					$vars['campaign_id'] = $end;
				}
				
				break;
			case 'items':
				$vars['campaign_id'] = $end;
				break;
			case 'message':
				if (is_numeric($end))
				{
					$vars['id'] = $end;
				}
				else
				{
					$vars['task'] = $end;
				}
				break;
			default:
				$vars['id'] = $end;
				break;
		}
		
		return $vars;
	}
}