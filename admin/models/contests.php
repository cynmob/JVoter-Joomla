<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of contest records.
 *
 * @since  1.6
 */
class JVoterModelContests extends JModelList
{

	/**
	 * Constructor.
	 *
	 * @param array $config
	 *        	An optional associative array of configuration settings.
	 *        	
	 * @see JController
	 * @since 1.6
	 */
	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
		    $config['filter_fields'] = array(
		        'id', 'jc.id',
		        'title', 'jc.title',
		        'alias', 'jc.alias',
		        'checked_out', 'jc.checked_out',
		        'checked_out_time', 'jc.checked_out_time',
		        'catid', 'jc.catid', 'category_title',
		        'state', 'jc.state',
		        'access', 'jc.access', 'access_level',
		        'created', 'jc.created',
		        'modified', 'jc.modified',
		        'created_by', 'jc.created_by',
		        'created_by_alias', 'jc.created_by_alias',
		        'ordering', 'jc.ordering',
		        'featured', 'jc.featured',		       
		        'hits', 'jc.hits',
		        'publish_up', 'jc.publish_up',
		        'publish_down', 'jc.publish_down',
		        'published', 'jc.published',
		        'author_id',
		        'category_id',
		        'level',		       
		    );
		}
		
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param string $ordering
	 *        	Elements order
	 * @param string $direction
	 *        	Order direction
	 *        	
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState ($ordering = 'jc.id', $direction = 'desc')
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		
		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
		    $this->context .= '.' . $layout;
		}
		
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
		
		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);
		
		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_jvoter');
		$this->setState('params', $params);
		
		$formSubmited = $app->input->post->get('form_submited');
		
		$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');		
		$planId = $this->getUserStateFromRequest($this->context . '.filter.plan_id', 'filter_plan_id');
		
		if ($formSubmited)
		{
		    $access = $app->input->post->get('access');
		    $this->setState('filter.access', $access);
		    
		    $authorId = $app->input->post->get('author_id');
		    $this->setState('filter.author_id', $authorId);
		    
		    $categoryId = $app->input->post->get('category_id');
		    $this->setState('filter.category_id', $categoryId);
		    
		    $planId = $app->input->post->get('plan_id');
		    $this->setState('filter.plan_id', $planId);
		}
		
		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param string $id
	 *        	A prefix for the store id.
	 *        	
	 * @return string A store id.
	 *        
	 * @since 1.6
	 */
	protected function getStoreId ($id = '')
	{
	    // Compile the store id.
	    $id .= ':' . $this->getState('filter.search');
	    $id .= ':' . serialize($this->getState('filter.access'));
	    $id .= ':' . $this->getState('filter.published');
	    $id .= ':' . serialize($this->getState('filter.category_id'));
	    $id .= ':' . serialize($this->getState('filter.author_id'));	
	    $id .= ':' . serialize($this->getState('filter.plan_id'));
	    
	    return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return JDatabaseQuery
	 *
	 * @since 1.6
	 */
	protected function getListQuery ()
	{		
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();
		
		// Select the required fields from the table.
		$query->select(
		    $this->getState(
		        'list.select',
		        'DISTINCT jc.id, jc.title, jc.alias, jc.checked_out, jc.checked_out_time, jc.catid' .
		        ', jc.state, jc.access, jc.created, jc.created_by, jc.created_by_alias, jc.modified, jc.ordering, jc.featured, jc.hits' .
		        ', jc.publish_up, jc.publish_down'
		    )
		);
		$query->from('#__jvoter_contests AS jc');
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
		      ->join('LEFT', '#__users AS uc ON uc.id=jc.checked_out');
		
		// Join over the asset groups.
		$query->select('ag.title AS access_level')
		      ->join('LEFT', '#__viewlevels AS ag ON ag.id = jc.access');
		
		// Join over the categories.
		$query->select('c.title AS category_title')
		      ->join('LEFT', '#__categories AS c ON c.id = jc.catid');
		
		// Join over the users for the author.
		$query->select('ua.name AS author_name')
		      ->join('LEFT', '#__users AS ua ON ua.id = jc.created_by');
		
        // Join over the plans.
        $query->select('p.name AS plan_name')
		      ->join('LEFT', '#__jvoter_plans AS p ON p.id = jc.planid');
		
        // Filter by access level.
        $access = $this->getState('filter.access');
        
        if (is_numeric($access))
        {
            $query->where('jc.access = ' . (int) $access);
        }
        elseif (is_array($access))
        {
            $access = ArrayHelper::toInteger($access);
            $access = implode(',', $access);
            $query->where('jc.access IN (' . $access . ')');
        }
        
        // Filter by access level on categories.
        if (!$user->authorise('core.admin'))
        {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('jc.access IN (' . $groups . ')');
            $query->where('c.access IN (' . $groups . ')');
        }		      
		      
        // Filter by published state
        $published = $this->getState('filter.published');
        
        if (is_numeric($published))
        {
            $query->where('jc.state = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(jc.state = 0 OR jc.state = 1)');
        }
		
        // Filter by categories and by level
        $categoryId = $this->getState('filter.category_id', array());
        $level = $this->getState('filter.level');
        
        if (!is_array($categoryId))
        {
            $categoryId = $categoryId ? array($categoryId) : array();
        }
        
        // Case: Using both categories filter and by level filter
        if (count($categoryId))
        {
            $categoryId = ArrayHelper::toInteger($categoryId);
            $categoryTable = JTable::getInstance('Category', 'JTable');
            $subCatItemsWhere = array();
            
            foreach ($categoryId as $filter_catid)
            {
                $categoryTable->load($filter_catid);
                $subCatItemsWhere[] = '(' .
                    ($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
                    'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
                    'c.rgt <= ' . (int) $categoryTable->rgt . ')';
            }
            
            $query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
        }
        
        // Case: Using only the by level filter
        elseif ($level)
        {
            $query->where('c.level <= ' . (int) $level);
        }
        
        // Filter by author
        $authorId = $this->getState('filter.author_id');
        
        if (is_numeric($authorId))
        {
            $type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
            $query->where('jc.created_by ' . $type . (int) $authorId);
        }
        elseif (is_array($authorId))
        {
            $authorId = ArrayHelper::toInteger($authorId);
            $authorId = implode(',', $authorId);
            $query->where('jc.created_by IN (' . $authorId . ')');
        }
        
        // Filter by search in title.
        $search = $this->getState('filter.search');
        
        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where('jc.id = ' . (int) substr($search, 3));
            }
            elseif (stripos($search, 'author:') === 0)
            {
                $search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
                $query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
            }
            elseif (stripos($search, 'content:') === 0)
            {
                $search = $db->quote('%' . $db->escape(substr($search, 8), true) . '%');
                $query->where('(jc.headertext LIKE ' . $search . ' OR jc.footertext LIKE ' . $search . ' OR jc.abouttext LIKE ' . $search . ')');
            }
            else
            {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(jc.title LIKE ' . $search . ' OR jc.alias LIKE ' . $search . ')');
            }
        }
        
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'jc.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
        return $query;
	}
}
