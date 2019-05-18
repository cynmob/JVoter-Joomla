<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of entry records.
 *
 * @since  1.6
 */
class JVoterModelEntries extends JModelList
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
		        'id', 'e.id',
		        'title', 'e.title',
		        'alias', 'e.alias',		       
		        'checked_out', 'e.checked_out',
		        'checked_out_time', 'e.checked_out_time',		      
		        'state', 'e.state',	
		        'status', 'e.status',
		        'created', 'e.created',
		        'modified', 'e.modified',
		        'created_by', 'e.created_by',		      
		        'ordering', 'e.ordering',
		        'author_id',
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
	protected function populateState ($ordering = 'e.id', $direction = 'desc')
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
			
		// Load the parameters.
		$params = JComponentHelper::getParams('com_jvoter');
		$this->setState('params', $params);
		
		$formSubmited = $app->input->post->get('form_submited');	
		
		$authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');		
		$contestId = $this->getUserStateFromRequest($this->context . '.filter.contest_id', 'filter_contest_id');
	
		if ($formSubmited)
		{
		    $authorId = $app->input->post->get('author_id');
		    $this->setState('filter.author_id', $authorId);
		    
		    $contestId = $app->input->post->get('contest_id');
		    $this->setState('filter.contest_id', $contestId);
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
	    $id .= ':' . $this->getState('filter.published');	 
	    $id .= ':' . serialize($this->getState('filter.author_id'));
	    $id .= ':' . serialize($this->getState('filter.contest_id'));
	    
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
		        'DISTINCT e.id, e.title, e.alias, e.contest_id, e.checked_out, e.checked_out_time' .
		        ', e.state, e.status, e.created, e.created_by, e.modified, e.ordering'		       
		    )
		);
		$query->from('#__jvoter_entries AS e');	
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
		      ->join('LEFT', '#__users AS uc ON uc.id=e.checked_out');
		      
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
		    $query->where('e.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
		    $query->where('(e.state = 0 OR e.state = 1)');
		}
		                
        // Filter by search in title.
        $search = $this->getState('filter.search');
        
        if (!empty($search))
        {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where('(e.title LIKE ' . $search . ' OR e.description LIKE ' . $search . ')');
        }
        
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'e.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
        return $query;
	}
}
