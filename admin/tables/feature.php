<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Feature Table class
 *
 * @since 1.6
 */
class JVoterTableFeature extends JTable
{

    /**
     * Constructor
     *
     * @param
     *            JDatabase &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jvoter_features', 'id', $db);
        
        // Set the alias since the column is called state
        $this->setColumnAlias('published', 'state');
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
            $this->setError(JText::_('COM_JVOTER_MUSTCONTAIN_A_TITLE_FEATURE'));
            
            return false;
        }
        
        if(empty($this->namekey))
        {
            $this->namekey = $this->title;
        }
        
        $this->namekey = JApplicationHelper::stringURLSafe($this->namekey, $this->language);
        
        if(trim(str_replace('-', '', $this->namekey)) == '')
        {
            $this->namekey = Joomla\String\StringHelper::increment($this->namekey, 'dash');
        }
        
        $this->namekey = str_replace(',', '-', $this->namekey);
        
        // Verify that the name is unique
        $table = JTable::getInstance('Feature', 'JVoterTable', array(
            'dbo' => $this->_db
        ));
        
        if($table->load(array(
            'namekey' => $this->namekey
        )) && ($table->id != $this->id || $this->id == 0))
        {
            $this->setError(JText::_('COM_JVOTER_ERROR_UNIQUE_FEATURE_NAME'));
            
            return false;
        }
        
        if(empty($this->label))
        {
            $this->label = $this->title;
        }
        
        if(empty($this->type))
        {
            $this->type = 'text';
        }
        
        if($this->type === 'boolean' && empty($this->value))
        {
            $this->value = '0';
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
