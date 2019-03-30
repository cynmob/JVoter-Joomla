<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * View class for a list of Dashboard.
 *
 * @since 1.6
 */
class JVoterViewDashboard extends JViewLegacy
{

    protected $items;

    protected $state;

    /**
     * Display the view
     *
     * @param string $tpl
     *            Template name
     *            
     * @return void
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        
        // Check for errors.
        if(count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors));
        }
        
        JVoterHelper::addSubmenu('dashboard');
        
        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since 1.6
     */
    protected function addToolbar()
    {
        $state = $this->get('State');
        $canDo = JVoterHelper::getActions();
        
        JToolBarHelper::title(JText::_('COM_JVOTER_TITLE_DASHBOARD'), 'home');
        
        if($canDo->get('core.admin'))
        {
            JToolBarHelper::preferences('com_jvoter');
        }
        
        // Set sidebar action - New in 3.0
        JHtmlSidebar::setAction('index.php?option=com_jvoter&view=dashboard');
    }

    /**
     * Check if state is set
     *
     * @param mixed $state
     *            State
     *            
     * @return bool
     */
    public function getState($state)
    {
        return isset($this->state->{$state}) ? $this->state->{$state} : false;
    }
}
