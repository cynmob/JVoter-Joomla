<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;

/**
 * JVoter Component Contest Model
 *
 * @since  1.5
 */
class JVoterModelContest extends JModelItem
{
    /**
     * Model context string.
     *
     * @var        string
     */
    protected $_context = 'com_jvoter.contest';
    
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     *
     * @return void
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('site');
        
        // Load state from the request.
        $pk = $app->input->getInt('id');
        $this->setState('contest.id', $pk);
        
        $offset = $app->input->getUInt('limitstart');
        $this->setState('list.offset', $offset);
        
        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);
        
        $user = JFactory::getUser();
        
        // If $pk is set then authorise on complete asset, else on component only
        $asset = empty($pk) ? 'com_jvoter' : 'com_jvoter.contest.' . $pk;
        
        if ((!$user->authorise('core.edit.state', $asset)) && (!$user->authorise('core.edit', $asset)))
        {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }
    
    /**
     * Method to get article data.
     *
     * @param   integer  $pk  The id of the article.
     *
     * @return  object|boolean|JException  Menu item data object on success, boolean false or JException instance on error
     */
    public function getItem($pk = null)
    {
        $user = JFactory::getUser();
        
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('contest.id');
        
        if ($this->_item === null)
        {
            $this->_item = array();
        }
        
        if (!isset($this->_item[$pk]))
        {
            try
            {
                $db = $this->getDbo();
                $query = $db->getQuery(true)
                ->select(
                    $this->getState(
                        'item.select', 'jc.id, jc.title, jc.alias, jc.headertext, jc.footertext, jc.abouttext, ' .
                        'jc.state, jc.catid, jc.created, jc.created_by, jc.created_by_alias, ' .
                        // Use created if modified is 0
                        'CASE WHEN jc.modified = ' . $db->quote($db->getNullDate()) . ' THEN jc.created ELSE jc.modified END as modified, ' .
                        'jc.modified_by, jc.checked_out, jc.checked_out_time, jc.publish_up, jc.publish_down, ' .
                        'jc.images, jc.attribs, jc.ordering, ' .
                        'jc.metakey, jc.metadesc, jc.access, jc.hits, jc.metadata, jc.featured'
                        )
                    );
                $query->from('#__content AS jc')
                ->where('jc.id = ' . (int) $pk);
                
                // Join on category table.
                $query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
                ->innerJoin('#__categories AS c on c.id = jc.catid')
                ->where('c.published > 0');
                
                // Join on user table.
                $query->select('u.name AS author')
                ->join('LEFT', '#__users AS u on u.id = jc.created_by');                                
                
                // Join over the categories to get parent category titles
                $query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias')
                ->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');
                                
                if ((!$user->authorise('core.edit.state', 'com_jvoter.contest.' . $pk)) && (!$user->authorise('core.edit', 'com_jvoter.contest.' . $pk)))
                {
                    // Filter by start and end dates.
                    $nullDate = $db->quote($db->getNullDate());
                    $date = JFactory::getDate();
                    
                    $nowDate = $db->quote($date->toSql());
                    
                    $query->where('(jc.publish_up = ' . $nullDate . ' OR jc.publish_up <= ' . $nowDate . ')')
                    ->where('(jc.publish_down = ' . $nullDate . ' OR jc.publish_down >= ' . $nowDate . ')');
                }
                
                // Filter by published state.
                $published = $this->getState('filter.published');
                $archived = $this->getState('filter.archived');
                
                if (is_numeric($published))
                {
                    $query->where('(jc.state = ' . (int) $published . ' OR jc.state =' . (int) $archived . ')');
                }
                
                $db->setQuery($query);
                
                $data = $db->loadObject();
                
                if (empty($data))
                {
                    return JError::raiseError(404, JText::_('COM_JVOTER_ERROR_CONTEST_NOT_FOUND'));
                }
                
                // Check for published state if filter set.
                if ((is_numeric($published) || is_numeric($archived)) && (($data->state != $published) && ($data->state != $archived)))
                {
                    return JError::raiseError(404, JText::_('COM_JVOTER_ERROR_CONTEST_NOT_FOUND'));
                }
                
                // Convert parameter fields to objects.
                $registry = new Registry($data->attribs);
                
                $data->params = clone $this->getState('params');
                $data->params->merge($registry);
                
                $data->metadata = new Registry($data->metadata);
                
                // Technically guest could edit an article, but lets not check that to improve performance a little.
                if (!$user->get('guest'))
                {
                    $userId = $user->get('id');
                    $asset = 'com_jvoter.contest.' . $data->id;
                    
                    // Check general edit permission first.
                    if ($user->authorise('core.edit', $asset))
                    {
                        $data->params->set('access-edit', true);
                    }
                    
                    // Now check if edit.own is available.
                    elseif (!empty($userId) && $user->authorise('core.edit.own', $asset))
                    {
                        // Check for a valid user and that they are the owner.
                        if ($userId == $data->created_by)
                        {
                            $data->params->set('access-edit', true);
                        }
                    }
                }
                
                // Compute view access permissions.
                if ($access = $this->getState('filter.access'))
                {
                    // If the access filter has been set, we already know this user can view.
                    $data->params->set('access-view', true);
                }
                else
                {
                    // If no access filter is set, the layout takes some responsibility for display of limited information.
                    $user = JFactory::getUser();
                    $groups = $user->getAuthorisedViewLevels();
                    
                    if ($data->catid == 0 || $data->category_access === null)
                    {
                        $data->params->set('access-view', in_array($data->access, $groups));
                    }
                    else
                    {
                        $data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
                    }
                }
                
                $this->_item[$pk] = $data;
            }
            catch (Exception $e)
            {
                if ($e->getCode() == 404)
                {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                }
                else
                {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }
        
        return $this->_item[$pk];
    }
    
    /**
     * Increment the hit counter for the contest.
     *
     * @param   integer  $pk  Optional primary key of the contest to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     */
    public function hit($pk = 0)
    {
        $input = JFactory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);
        
        if ($hitcount)
        {
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('contest.id');
                       
            JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
            $table = JTable::getInstance('Contest', 'JVoterTable');
            $table->load($pk);
            $table->hit($pk);
        }
        
        return true;
    }
}
