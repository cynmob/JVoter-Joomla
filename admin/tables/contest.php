<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Observer\ContentHistory as ContentHistoryObserver;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Contest Table class
 *
 * @since 1.6
 */
class JVoterTableContest extends JTable
{

    /**
     * Constructor
     *
     * @param
     *            JDatabase &$db A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__jvoter_contests', 'id', $db);
        
        JTableObserverContenthistory::createObserver($this, array('typeAlias' => 'com_jvoter.contest'));   
        
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
        if (isset($array['attribs']) && is_array($array['attribs']))
        {
            $registry = new Registry($array['attribs']);
            $array['attribs'] = (string) $registry;
        }
        
        if (isset($array['metadata']) && is_array($array['metadata']))
        {
            $registry = new Registry($array['metadata']);
            $array['metadata'] = (string) $registry;
        }
        
        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules']))
        {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }      
        
        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules']))
        {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }
        
        return parent::bind($array, $ignore);
    }    

    /**
     * Overloaded check function
     *
     * @return bool
     */
    public function check()
    {
        if (trim($this->title) == '')
        {
            $this->setError(\JText::_('COM_JVOTER_WARNING_PROVIDE_VALID_NAME'));
            
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
        
        /**
         * Ensure any new items have compulsory fields set. This is needed for things like
         * frontend editing where we don't show all the fields or using some kind of API
         */
        if (!$this->id)
        {
            // Images can be an empty json string
            if (!isset($this->images))
            {
                $this->images = '{}';
            }
                        
            // Attributes (article params) can be an empty json string
            if (!isset($this->attribs))
            {
                $this->attribs = '{}';
            }
            
            // Metadata can be an empty json string
            if (!isset($this->metadata))
            {
                $this->metadata = '{}';
            }
        }
        
        // Check the publish down date is not earlier than publish up.
        if ($this->publish_down < $this->publish_up && $this->publish_down > $this->_db->getNullDate())
        {
            // Swap the dates.
            $temp = $this->publish_up;
            $this->publish_up = $this->publish_down;
            $this->publish_down = $temp;
        }
        
        // Clean up keywords -- eliminate extra spaces between phrases
        // and cr (\r) and lf (\n) characters from string
        if (!empty($this->metakey))
        {
            // Only process if not empty
            
            // Array of characters to remove
            $bad_characters = array("\n", "\r", "\"", '<', '>');
            
            // Remove bad characters
            $after_clean = StringHelper::str_ireplace($bad_characters, '', $this->metakey);
            
            // Create array using commas as delimiter
            $keys = explode(',', $after_clean);
            
            $clean_keys = array();
            
            foreach ($keys as $key)
            {
                if (trim($key))
                {
                    // Ignore blank keywords
                    $clean_keys[] = trim($key);
                }
            }
            
            // Put array back together delimited by ", "
            $this->metakey = implode(', ', $clean_keys);
        }
        
        return true;
    }
    
    /**
     * Define a namespaced asset name for inclusion in the #__assets table
     *
     * @return string The asset name
     *        
     * @see JTable::_getAssetName
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        
        return 'com_jvoter.contest.' . (int) $this->$k;
    }
    
    /**
     * Method to get the parent asset id for the record
     *
     * @param   Table    $table  A Table object (optional) for the asset parent
     * @param   integer  $id     The id (optional) of the content.
     *
     * @return  integer
     *
     * @since   1.6
     * @deprecated  3.1.4 Class will be removed upon completion of transition to UCM
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $assetId = null;
        
        // This is an article under a category.
        if ($this->catid)
        {
            // Build the query to get the asset id for the parent category.
            $query = $this->_db->getQuery(true)
            ->select($this->_db->quoteName('asset_id'))
            ->from($this->_db->quoteName('#__categories'))
            ->where($this->_db->quoteName('id') . ' = ' . (int) $this->catid);
            
            // Get the asset id from the database.
            $this->_db->setQuery($query);
            
            if ($result = $this->_db->loadResult())
            {
                $assetId = (int) $result;
            }
        }
        
        // Return the asset id.
        if ($assetId)
        {
            return $assetId;
        }
        else
        {
            return parent::_getAssetParentId($table, $id);
        }
    }
}
