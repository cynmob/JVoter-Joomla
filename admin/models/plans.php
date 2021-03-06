<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of plan records.
 *
 * @since  1.6
 */
class JVoterModelPlans extends JModelList
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
		        'id', 'p.id',
		        'title', 'p.title',
		        'price', 'p.price',
		        'checked_out', 'p.checked_out',
		        'checked_out_time', 'p.checked_out_time',		      
		        'state', 'p.state',
		        'created', 'p.created',
		        'modified', 'p.modified',
		        'created_by', 'p.created_by',		      
		        'ordering', 'p.ordering'		       		       
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
	protected function populateState ($ordering = 'p.id', $direction = 'desc')
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
		
		// Select the required fields from the table.
		$query->select(
		    $this->getState(
		        'list.select',
		        'DISTINCT p.id, p.title, p.price, p.checked_out, p.checked_out_time' .
		        ', p.state, p.created, p.created_by, p.modified, p.ordering'		       
		    )
		);
		$query->from('#__jvoter_plans AS p');	
		
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
		      ->join('LEFT', '#__users AS uc ON uc.id=p.checked_out');
		      
        // Filter by published state
        $published = $this->getState('filter.published');
        
        if (is_numeric($published))
        {
            $query->where('p.state = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(p.state = 0 OR p.state = 1)');
        }
                
        // Filter by search in title.
        $search = $this->getState('filter.search');
        
        if (!empty($search))
        {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where('(p.title LIKE ' . $search . ' OR p.description LIKE ' . $search . ')');
        }
        
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'p.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
        return $query;
	}
}
