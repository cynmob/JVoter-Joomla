<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * View class for a list of features.
 *
 * @since  1.6
 */
class JVoterViewEntries extends JViewLegacy
{   
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;
    
    /**
     * The pagination object
     *
     * @var  JPagination
     */
    protected $pagination;
    
    /**
     * The model state
     *
     * @var  object
     */
    protected $state;
    
    /**
     * Form object for search filters
     *
     * @var  JForm
     */
    public $filterForm;
    
    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;
    
    /**
     * The sidebar markup
     *
     * @var  string
     */
    protected $sidebar;
    
    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        if ($this->getLayout() !== 'modal')
        {
            JVoterHelper::addSubmenu('entries');
        }
        
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');        
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
             
        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {           
            throw new Exception(implode("\n", $errors), 500);
        }
              
        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
        
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
        $canDo = JVoterHelper::getActions();
        $user  = JFactory::getUser();
        
        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');
        
        JToolbarHelper::title(JText::_('COM_JVOTER_TITLE_ENTRIES'), 'puzzle-piece entry');
        
        if ($canDo->get('core.create'))
        {
            JToolbarHelper::addNew('entry.add');
        }
        
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
        {
            JToolbarHelper::editList('entries.edit');
        }
        
        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('entries.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('entries.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::archiveList('entries.archive');
        }
        
        if ($user->authorise('core.admin'))
        {
            JToolbarHelper::checkin('entries.checkin');
        }                    
                
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'entries.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('entries.trash');
        }
        
        if ($user->authorise('core.admin', 'com_jvoter') || $user->authorise('core.options', 'com_jvoter'))
        {
            JToolbarHelper::preferences('com_jvoter');
        }
    }
    
    /**
     * Returns the sort fields.
     *
     * @return  array
     *
     * @since   3.7.0
     */
    protected function getSortFields()
    {
        return array(
            'e.ordering' => JText::_('JGRID_HEADING_ORDERING'),
            'e.state'    => JText::_('JSTATUS'),
            'e.title'    => JText::_('JGLOBAL_TITLE'),
            'e.type'     => JText::_('COM_JVOTER_FIELD_TYPE_LABEL'),               
            'e.id'       => JText::_('JGRID_HEADING_ID'),
        );
    }
}
