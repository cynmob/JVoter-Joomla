<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

use Joomla\Utilities\ArrayHelper;

/**
 * Class JVoterController
 *
 * @since 1.6
 */
class JVoterController extends JControllerLegacy
{

	/**
	 * Constructor.
	 *
	 * @param array $config
	 *        	An optional associative array of configuration settings.
	 *        	
	 * @see \JControllerLegacy
	 * @since 1.6
	 * @throws \Exception
	 */
	public function __construct ($config = array())
	{
		parent::__construct($config);
		
		// Define standard task mappings.
		
		// Value = 0
		$this->registerTask('unpublish', 'publish');
		
		// Guess the option as com_NameOfController.
		if (empty($this->option))
		{
			$this->option = 'com_' . strtolower($this->getName());
		}
		
		// Guess the \JText message prefix. Defaults to the option.
		if (empty($this->text_prefix))
		{
			$this->text_prefix = strtoupper($this->option);
		}
		
		// Guess the list view as the suffix, eg: OptionControllerSuffix.
		if (empty($this->view_list))
		{
			$r = null;
			
			if (! preg_match('/(.*)Controller(.*)/i', get_class($this), $r))
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_CONTROLLER_GET_NAME'), 500);
			}
			
			$this->view_list = strtolower($r[2]);
		}
	}

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
		$app = JFactory::getApplication();
		$view = $app->input->getCmd('view', 'contests');
		$app->input->set('view', $view);
		
		parent::display($cachable, $urlparams);
		
		return $this;
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return void
	 *
	 * @since 1.6
	 */
	public function publish ()
	{
		// Check for request forgeries
		\JSession::checkToken() or die(\JText::_('JINVALID_TOKEN'));
		
		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$data = array(
				'publish' => 1,
				'unpublish' => 0,
				'archive' => 2,
				'trash' => - 2,
				'report' => - 3
		);
		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');
		
		if (empty($cid))
		{
			\JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
			
			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);
			
			// Publish the items.
			try
			{
				$model->publish($cid, $value);
				$errors = $model->getErrors();
				$ntext = null;
				
				if ($value === 1)
				{
					if ($errors)
					{
						\JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)),
								'error');
					}
					else
					{
						$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
					}
				}
				elseif ($value === 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value === 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}
				
				if ($ntext !== null)
				{
					$this->setMessage(\JText::plural($ntext, count($cid)));
				}
			}
			catch (\Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}
		
		$this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
}
