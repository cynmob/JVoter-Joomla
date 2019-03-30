<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class JVoterController
 *
 * @since 1.6
 */
class JVoterController extends JControllerLegacy
{

	/**
	 * Method to display a view.
	 *
	 * @param boolean $cachable
	 *        	If true, the view output will be cached
	 * @param mixed $urlparams
	 *        	An array of safe url parameters and their variable types, for
	 *        	valid values see {@link JFilterInput::clean()}.
	 *        	
	 * @return JController This object to support chaining.
	 *        
	 * @since 1.5
	 */
	public function display ($cachable = false, $urlparams = false)
	{
		$view = $this->input->getCmd('view', 'dashboard');
		$this->input->set('view', $view);
		
		parent::display($cachable, $urlparams);
		
		return $this;
	}
}
