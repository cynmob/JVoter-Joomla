<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\Application\ApplicationHelper;

/**
 * Entry Table class
 *
 * @since 1.6
 */
class JVoterTableEntry extends JTable
{

    /**
     * Constructor
     *
     * @param
     *            JDatabase &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jvoter_entries', 'id', $db);
        
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
        if (isset($array['images']) && is_array($array['images']))
        {            
            $registry = new Registry($array['images']);
            $array['images'] = (string) $registry;
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
            $this->setError(JText::_('COM_JVOTER_MUSTCONTAIN_A_TITLE_ENTRY'));
            
            return false;
        }      
        
        if (trim($this->alias) == '')
        {
            $this->alias = $this->title;
        }
        
        $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        
        if (trim(str_replace('-', '', $this->alias)) == '')
        {
            $this->alias = \JFactory::getDate()->format('Y-m-d-H-i-s');
        }
        
        if (!$this->id)
        {
            // Images can be an empty json string
            if (!isset($this->images))
            {
                $this->images = '{}';
            }
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
