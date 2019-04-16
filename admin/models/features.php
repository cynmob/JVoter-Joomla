<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of feature records.
 *
 * @since  1.6
 */
class JVoterModelFeatures extends JModelList
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
		        'id', 'f.id',
		        'title', 'f.title',
		        'label', 'f.label',
		        'namekey', 'f.namekey',
		        'type', 'f.type',
		        'checked_out', 'f.checked_out',
		        'checked_out_time', 'f.checked_out_time',		      
		        'state', 'f.state',		       
		        'created', 'f.created',
		        'modified', 'f.modified',
		        'created_by', 'f.created_by',		      
		        'ordering', 'f.ordering'		       		       
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
	protected function populateState ($ordering = 'f.id', $direction = 'desc')
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
	    $id .= ':' . serialize($this->getState('filter.type'));
	    
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
		        'DISTINCT f.id, f.title, f.label, f.namekey, f.type, f.value, f.checked_out, f.checked_out_time' .
		        ', f.state, f.created, f.created_by, f.modified, f.ordering'		       
		    )
		);
		$query->from('#__jvoter_features AS f');	
		
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
		      ->join('LEFT', '#__users AS uc ON uc.id=f.checked_out');
		      
		// Filter by published state
		$published = $this->getState('filter.published');
		
		if (is_numeric($published))
		{
		    $query->where('f.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
		    $query->where('(f.state = 0 OR f.state = 1)');
		}
		
		// Filter by type		
		if($type = $this->getState('filter.type'))
		{
		    $query->where('f.type = ' . $db->quote($type));
		}
                
        // Filter by search in title.
        $search = $this->getState('filter.search');
        
        if (!empty($search))
        {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where('(f.label LIKE ' . $search . ' OR f.namekey LIKE ' . $search . ' OR f.value LIKE ' . $search . ' OR f.description LIKE ' . $search . ')');
        }
        
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'f.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));
        
        return $query;
	}
}
