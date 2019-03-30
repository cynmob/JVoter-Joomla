<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * View to edit an contest.
 *
 * @since  1.6
 */
class JVoterViewContest extends JViewLegacy
{
    /**
     * The JForm object
     *
     * @var  JForm
     */
    protected $form;
    
    /**
     * The active item
     *
     * @var  object
     */
    protected $item;
    
    /**
     * The model state
     *
     * @var  object
     */
    protected $state;
    
    /**
     * The actions the user is authorised to perform
     *
     * @var  JObject
     */
    protected $canDo;
    
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {        
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $this->canDo = JVoterHelper::getActions('contest', $this->item->id);
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }
        
        // If we are forcing a language in modal (used for associations).
        if ($this->getLayout() === 'modal' && $forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'cmd'))
        {
            // Set the language field to the forcedLanguage and disable changing it.
            $this->form->setValue('language', null, $forcedLanguage);
            $this->form->setFieldAttribute('language', 'readonly', 'true');
            
            // Only allow to select categories with All language or with the forced language.
            $this->form->setFieldAttribute('catid', 'language', '*,' . $forcedLanguage);
        }
        
        $this->addToolbar();
        
        return parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $user       = JFactory::getUser();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);
        $checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
        
        // Built the actions for new and existing records.
        $canDo = $this->canDo;
        
        JToolbarHelper::title(
            JText::_('COM_JVOTER_PAGE_' . ($checkedOut ? 'VIEW_CONTEST' : ($isNew ? 'ADD_CONTEST' : 'EDIT_CONTEST'))),
            'pencil-2 contest-add'
            );
        
        // For new records, check the create permission.
        if ($isNew && (count($user->getAuthorisedCategories('com_jvoter', 'core.create')) > 0))
        {
            JToolbarHelper::apply('contest.apply');
            JToolbarHelper::save('contest.save');
            JToolbarHelper::save2new('contest.save2new');
            JToolbarHelper::cancel('contest.cancel');
        }
        else
        {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
            
            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable)
            {
                JToolbarHelper::apply('contest.apply');
                JToolbarHelper::save('contest.save');
                
                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create'))
                {
                    JToolbarHelper::save2new('contest.save2new');
                }
            }
            
            // If checked out, we can still save
            if ($canDo->get('core.create'))
            {
                JToolbarHelper::save2copy('contest.save2copy');
            }
                      
            JToolbarHelper::cancel('contest.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
