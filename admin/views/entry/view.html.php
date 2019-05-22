<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * View to edit an feature.
 *
 * @since  1.6
 */
class JVoterViewEntry extends JViewLegacy
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
        $this->canDo = JVoterHelper::getActions('entry', $this->item->id);
        
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
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
            JText::sprintf('COM_JVOTER_PAGE_' . ($checkedOut ? 'VIEW_ITEM' : ($isNew ? 'ADD_ITEM' : 'EDIT_ITEM')), JText::_('COM_JVOTER_PAGE_TITLE_ENTRY')),
            'pencil-square-o entry-add'
            );
        
        // For new records, check the create permission.
        if ($isNew)
        {
            JToolbarHelper::apply('entry.apply');
            JToolbarHelper::save('entry.save');
            JToolbarHelper::save2new('entry.save2new');
        }
        
        // If not checked out, can save the item.
        elseif (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId)))
        {
            JToolbarHelper::apply('entry.apply');
            JToolbarHelper::save('entry.save');
            
            if ($canDo->get('core.create'))
            {
                JToolbarHelper::save2new('entry.save2new');
            }
        }
        
        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create'))
        {
            JToolbarHelper::save2copy('entry.save2copy');
        }
        
        if (empty($this->item->id))
        {
            JToolbarHelper::cancel('entry.cancel');
        }
        else
        {
            JToolbarHelper::cancel('entry.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}
