<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

JLoader::register('JVoterHelper', JPATH_ADMINISTRATOR . '/components/com_jvoter/helpers/jvoter.php');

/**
 * Item Model for an Feature.
 *
 * @since  1.6
 */
class JVoterModelFeature extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_JVOTER';
    
    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_jvoter.feature'; 
          
    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if (!empty($record->id))
        {
            if ($record->state != -2)
            {
                return false;
            }
            
            return JFactory::getUser()->authorise('core.delete', 'com_jvoter.feature.' . (int) $record->id);
        }
        
        return false;
    }
    
    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        $user = JFactory::getUser();
        
        // Check for existing article.
        if (!empty($record->id))
        {
            return $user->authorise('core.edit.state', 'com_jvoter.feature.' . (int) $record->id);
        }
              
        // Default to component settings if neither article nor category known.
        return parent::canEditState($record);
    }
        
    /**
     * Returns a Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable    A database object
     */
    public function getTable($type = 'Feature', $prefix = 'JVoterTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
    
       
    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_jvoter.feature', 'feature', array('control' => 'jform', 'load_data' => $loadData));
        
        if (empty($form))
        {
            return false;
        }
        
        $jinput = JFactory::getApplication()->input;
        
        /*
         * The front end calls this model and uses a_id to avoid id clashes so we need to check for that first.
         * The back end uses id so we use that the rest of the time and set it to 0 by default.
         */
        $id = $jinput->get('a_id', $jinput->get('id', 0));
        
        // Determine correct permissions to check.
        if ($this->getState('feature.id'))
        {
            $id = $this->getState('feature.id');            
        }
        
        $user = JFactory::getUser();
        
        // Check for existing article.
        // Modify the form based on Edit State access controls.
        if ($id != 0 && (!$user->authorise('core.edit.state', 'com_jvoter.feature.' . (int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', 'com_jvoter')))
        {
            // Disable fields for display.           
            $form->setFieldAttribute('ordering', 'disabled', 'true');          
            $form->setFieldAttribute('state', 'disabled', 'true');
            
            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.          
            $form->setFieldAttribute('ordering', 'filter', 'unset');         
            $form->setFieldAttribute('state', 'filter', 'unset');
        }        
                
        return $form;
    }
    
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app  = JFactory::getApplication();
        $data = $app->getUserState('com_jvoter.edit.feature.data', array());
        
        if (empty($data))
        {
            $data = $this->getItem();
            
            // Pre-select some filters (Status) in edit form if those have been selected in JVoter: Contests
            if ($this->getState('feature.id') == 0)
            {
                $filters = (array) $app->getUserState('com_jvoter.features.filter');
                $data->set(
                    'state',
                    $app->input->getInt(
                        'state',
                        ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                        )
                    );
            }
        }
        
        
        $this->preprocessData('com_jvoter.feature', $data);
        
        return $data;
    }
    
    /**
     * Method to validate the form data.
     *
     * @param   JForm   $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     JFormRule
     * @see     JFilterInput
     * @since   3.7.0
     */
    public function validate($form, $data, $group = null)
    {
        // Don't allow to change the users if not allowed to access com_users.
        if (JFactory::getApplication()->isClient('administrator') && !JFactory::getUser()->authorise('core.manage', 'com_users'))
        {
            if (isset($data['created_by']))
            {
                unset($data['created_by']);
            }
            
            if (isset($data['modified_by']))
            {
                unset($data['modified_by']);
            }
        }
        
        return parent::validate($form, $data, $group);
    }
    
    
    /**
     * Allows preprocessing of the JForm object.
     *
     * @param   JForm   $form   The form object
     * @param   array   $data   The data to be merged into the form object
     * @param   string  $group  The plugin group to be executed
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function preprocessForm(JForm $form, $data, $group = 'feature')
    {        
        parent::preprocessForm($form, $data, $group);
    }
}
