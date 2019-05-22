<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

/**
 * Contests list controller class.
 *
 * @since 1.6
 */
class JVoterControllerContests extends JControllerAdmin
{

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JControllerLegacy
     * @since   1.6
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
      
        $this->registerTask('unfeatured', 'featured');
        $this->registerTask('disapproved', 'approved');
    }
    
    /**
     * Method to toggle the featured setting of a list of contests.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function featured()
    {
        // Check for request forgeries
        $this->checkToken();
        
        $user   = JFactory::getUser();
        $ids    = $this->input->get('cid', array(), 'array');
        $values = array('featured' => 1, 'unfeatured' => 0);
        $task   = $this->getTask();
        $value  = ArrayHelper::getValue($values, $task, 0, 'int');
        
        // Access checks.
        foreach ($ids as $i => $id)
        {
            if (!$user->authorise('core.edit.state', 'com_jvoter.contest.' . (int) $id))
            {
                // Prune items that you can't change.
                unset($ids[$i]);              
                $this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');               
            }
        }
        
        if (empty($ids))
        {           
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        }
        else
        {
            // Get the model.
            /** @var JVoterModelContests $model */
            $model = $this->getModel();
            
            // Publish the items.
            if (!$model->featured($ids, $value))
            {              
                $this->setMessage($model->getError(), 'error');
            }
            
            if ($value == 1)
            {
                $message = JText::plural('COM_JVOTER_N_ITEMS_FEATURED', count($ids));
            }
            else
            {
                $message = JText::plural('COM_JVOTER_N_ITEMS_UNFEATURED', count($ids));
            }
        }
        
        $this->setRedirect(JRoute::_('index.php?option=com_jvoter&view=contests', false), $message);
    }
    
    /**
     * Method to toggle the featured setting of a list of articles.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function approved()
    {
        // Check for request forgeries
        $this->checkToken();
        
        $user   = JFactory::getUser();
        $ids    = $this->input->get('cid', array(), 'array');
        $values = array('approved' => 1, 'disapproved' => 0);
        $task   = $this->getTask();
        $value  = ArrayHelper::getValue($values, $task, 0, 'int');
        
        // Access checks.
        foreach ($ids as $i => $id)
        {
            if (!$user->authorise('core.edit.state', 'com_jvoter.contest.' . (int) $id))
            {
                // Prune items that you can't change.
                unset($ids[$i]);
                $this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'error');
            }
        }
        
        if (empty($ids))
        {
            \JLog::add(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), \JLog::WARNING, 'jerror');
        }
        else
        {
            // Get the model.
            /** @var JVoterModelContest $model */
            $model = $this->getModel();
            
            // Publish the items.
            if (!$model->moderated($ids, $value))
            {
                $this->setMessage($model->getError(), 'error');
            }
            
            if ($value == 1)
            {
                $message = JText::plural('COM_JVOTER_N_ITEMS_APPROVE', count($ids));
            }
            else
            {
                $message = JText::plural('COM_JVOTER_N_ITEMS_DISAPPROVE', count($ids));
            }
        }
        
        $this->setRedirect(JRoute::_('index.php?option=com_jvoter&view=contests', false), $message);
    }
    
    /**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function publish()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
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
						\JFactory::getApplication()->enqueueMessage(\JText::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
					}
					else
					{
						$ntext = $this->text_prefix . '_N_CONTESTS_PUBLISHED';
					}
				}
				elseif ($value === 0)
				{
					$ntext = $this->text_prefix . '_N_CONTESTS_UNPUBLISHED';
				}
				elseif ($value === 2)
				{
					$ntext = $this->text_prefix . '_N_CONTESTS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_CONTESTS_TRASHED';
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

    /**
     * Proxy for getModel.
     *
     * @param string $name
     *            Optional. Model name
     * @param string $prefix
     *            Optional. Class prefix
     * @param array $config
     *            Optional. Configuration array for model
     *            
     * @return object The Model
     *        
     * @since 1.6
     */
    public function getModel($name = 'contest', $prefix = 'JVoterModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array(
            'ignore_request' => true
        ));
        
        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return void
     *
     * @since 3.0
     */
    public function saveOrderAjax()
    {
        // Get the input
        $pks = $this->input->post->get('cid', array(), 'array');
        $order = $this->input->post->get('order', array(), 'array');
        
        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);
        
        // Get the model
        $model = $this->getModel();
        
        // Save the ordering
        $return = $model->saveorder($pks, $order);
        
        if($return)
        {
            echo "1";
        }
        
        // Close the application
        $this->app->close();
    }
}
