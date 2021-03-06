<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;

jimport('joomla.form.formfield');

class JFormFieldFeatures extends JFormField
{
    
    /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
    protected $type = 'features';
    
    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup.
     *
     * @since 1.6
     */
    protected function getInput()
    {                     
        $displayData = array();
        $db = JFactory::getDBO();
        
        $query = $db->getQuery(true);
        $query->select('*')
        ->from('#__jvoter_features')
        ->where('state = 1');
        $db->setQuery($query);
        $displayData['features'] = $db->loadObjectList();                
        
        if (!empty($this->value) && !($this->value instanceof Registry))
        {
            $registry = new Registry($this->value);
            $this->value = $registry;
            
            //process $displayData
        }
        
        $displayData['view'] = $this;
              
        return JLayoutHelper::render('jvoter.form.field.features', $displayData);
    }
}
