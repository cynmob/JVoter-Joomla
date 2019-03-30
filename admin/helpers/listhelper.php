<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

/**
 * JVoter Listhelper.
 *
 * @since 1.6
 */
abstract class JHtmlListhelper
{

    static function toggle($value = 0, $view, $field, $i)
    {
        $states = array(
            0 => array(
                'icon-remove',
                JText::_('COM_JVOTER_TOGGLE'),
                'inactive btn-danger'
            ),
            1 => array(
                'icon-checkmark',
                JText::_('COM_JVOTER_TOGGLE'),
                'active btn-success'
            )
        );
        
        $state = \Joomla\Utilities\ArrayHelper::getValue($states, (int) $value, $states[0]);
        $text = '<span aria-hidden="true" class="' . $state[0] . '"></span>';
        $html = '<a href="#" class="btn btn-micro ' . $state[2] . '"';
        $html .= 'onclick="return toggleField(\'cb' . $i . '\',\'' . $view . '.toggle\',\'' . $field . '\')" title="' . JText::_($state[1]) . '">' . $text . '</a>';
        
        return $html;
    }
}
