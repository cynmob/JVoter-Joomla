<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * View class for a list of contests.
 *
 * @since  1.6
 */
class JVoterViewContests extends JViewLegacy
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
            JVoterHelper::addSubmenu('contests');
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
              
        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal')
        {
            $this->addToolbar();
            $this->sidebar = JHtmlSidebar::render();
        }
        else
        {
            // In article associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show show categories with All or the forced language.
            if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD'))
            {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                $languageXml = new SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);
                
                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);
                
                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
            }
        }
        
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
        $canDo = JVoterHelper::getActions('category', $this->state->get('filter.category_id'));
        $user  = JFactory::getUser();
        
        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');
        
        JToolbarHelper::title(JText::_('COM_JVOTER_TITLE_CONTESTS'), 'trophy contest');
        
        if ($canDo->get('core.create') || count($user->getAuthorisedCategories('com_jvoter', 'core.create')) > 0)
        {
            JToolbarHelper::addNew('contest.add');
        }
        
        if ($canDo->get('core.edit') || $canDo->get('core.edit.own'))
        {
            JToolbarHelper::editList('contest.edit');
        }
        
        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('contests.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('contests.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::custom('contests.featured', 'featured.png', 'featured_f2.png', 'JFEATURE', true);
            JToolbarHelper::custom('contests.unfeatured', 'unfeatured.png', 'featured_f2.png', 'JUNFEATURE', true);
            JToolbarHelper::archiveList('contests.archive');
            JToolbarHelper::checkin('contests.checkin');
        }        
                
        if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'contests.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('contests.trash');
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
            'jc.ordering'     => JText::_('JGRID_HEADING_ORDERING'),
            'jc.state'        => JText::_('JSTATUS'),
            'jc.title'        => JText::_('JGLOBAL_TITLE'),
            'category_title' => JText::_('JCATEGORY'),
            'access_level'   => JText::_('JGRID_HEADING_ACCESS'),
            'jc.created_by'   => JText::_('JAUTHOR'),         
            'jc.created'      => JText::_('JDATE'),
            'jc.id'           => JText::_('JGRID_HEADING_ID'),
            'jc.featured'     => JText::_('JFEATURED')
        );
    }
}
