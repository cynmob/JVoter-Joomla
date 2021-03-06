<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * View class for a list of plans.
 *
 * @since  1.6
 */
class JVoterViewPlans extends JViewLegacy
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
            JVoterHelper::addSubmenu('plans');
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
        
        JToolbarHelper::title(JText::_('COM_JVOTER_TITLE_PLANS'), 'archive plan');
        
        if ($canDo->get('core.create'))
        {
            JToolbarHelper::addNew('plan.add');
        }
        
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
        {
            JToolbarHelper::editList('plans.edit');
        }
        
        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('plans.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('plans.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::archiveList('plans.archive');
        }
        
        if ($user->authorise('core.admin'))
        {
            JToolbarHelper::checkin('plans.checkin');
        }
        
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'plans.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('plans.trash');
        }
        
        if ($user->authorise('core.admin', 'com_jvoter') || $user->authorise('core.options', 'com_jvoter'))
        {
            JToolbarHelper::preferences('com_jvoter');
        }
    }
    
    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'p.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
            'p.state'        => JText::_('JSTATUS'),
            'p.title'        => JText::_('JGLOBAL_TITLE'),
            'p.price'        => JText::_('COM_JVOTER_FIELD_PRICE_LABEL'),
            'p.created_by'   => JText::_('JAUTHOR'),
            'p.created'      => JText::_('JDATE'),
            'p.id'           => JText::_('JGRID_HEADING_ID')
        );
    }
}
