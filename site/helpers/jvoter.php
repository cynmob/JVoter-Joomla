<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class JVoterHelperSite
 *
 * @since 1.6
 */
abstract class JVoterHelperSite
{

    /**
     * Get an instance of the named model
     *
     * @param string $name
     *            Model name
     *            
     * @return null|object
     */
    public static function getModel($name)
    {
        $model = null;
        
        // If the file exists, let's
        if(file_exists(JPATH_SITE . '/components/com_jvoter/models/' . strtolower($name) . '.php'))
        {
            require_once JPATH_SITE . '/components/com_jvoter/models/' . strtolower($name) . '.php';
            $model = JModelLegacy::getInstance($name, 'JVoterModel');
        }
        
        return $model;
    }

    /**
     * Gets the edit permission for an user
     *
     * @param mixed $item
     *            The item
     *            
     * @return bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user = JFactory::getUser();
        
        if($user->authorise('core.edit', 'com_jvoter'))
        {
            $permission = true;
        } else
        {
            if(isset($item->created_by))
            {
                if($user->authorise('core.edit.own', 'com_jvoter') && $item->created_by == $user->id)
                {
                    $permission = true;
                }
            } else
            {
                $permission = true;
            }
        }
        
        return $permission;
    }
}
