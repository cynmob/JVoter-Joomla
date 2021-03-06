<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

/**
 * Feature Table class
 *
 * @since 1.6
 */
class JVoterTablePlan extends JTable
{

    /**
     * Constructor
     *
     * @param
     *            JDatabase &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jvoter_plans', 'id', $db);
        
        // Set the alias since the column is called state
        $this->setColumnAlias('published', 'state');
    }
    
    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param array $array
     *            Named array
     * @param mixed $ignore
     *            Optional array or list of parameters to ignore
     *
     * @return null|string null is operation was satisfactory, otherwise returns
     *         an error
     *
     * @see JTable:bind
     * @since 1.5
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['features']) && is_array($array['features']))
        {
            // only add features that are checked
            $cid = JFactory::getApplication()->input->get('cid');
            $features = array();
            foreach($cid as $id)
            {
                $features[$id] = $array['features'][$id];
            }
            
            $registry = new Registry($features);
            $array['features'] = (string) $registry;
        }       
        
        return parent::bind($array, $ignore);
    }    
    
    /**
     * Method to perform sanity checks on the JTable instance properties to ensure
     * they are safe to store in the database.
     * Child classes should override this
     * method to make sure the data they are storing in the database is safe and
     * as expected before storage.
     *
     * @return boolean True if the instance is sane and able to be stored in the database.
     *
     * @link https://docs.joomla.org/Special:MyLanguage/JTable/check
     * @since 3.7.0
     */
    public function check()
    {
        // Check for valid name
        if(trim($this->title) == '')
        {
            $this->setError(JText::_('COM_JVOTER_MUSTCONTAIN_A_TITLE_PLAN'));
            
            return false;
        }      
        
        $date = JFactory::getDate();
        $user = JFactory::getUser();
        
        if($this->id)
        {
            // Existing item
            $this->modified = $date->toSql();
            $this->modified_by = $user->get('id');
        } else
        {
            if(! (int) $this->created)
            {
                $this->created = $date->toSql();
            }
            
            if(empty($this->created_by))
            {
                $this->created_by = $user->get('id');
            }
        }
        
        return true;
    }
}
