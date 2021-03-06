<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controllerform');

/**
 * Plan controller class.
 *
 * @since 1.6
 */
class JVoterControllerPlan extends JControllerForm
{

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->view_list = 'plans';
        parent::__construct();
    }
}
