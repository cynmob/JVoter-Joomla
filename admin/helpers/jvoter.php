<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * JVoter helper.
 *
 * @since 1.6
 */
abstract class JVoterHelper
{
    
    /**
     * Configure the Linkbar.
     *
     * @param string $vName
     *            string
     *
     * @return void
     */
    public static function addSubmenu($vName = '')
    {
        JHtmlSidebar::addEntry(JText::_('COM_JVOTER_TITLE_DASHBOARD'), 'index.php?option=com_jvoter&view=dashboard', $vName == 'dashboard');
        JHtmlSidebar::addEntry(JText::_('COM_JVOTER_TITLE_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_jvoter', $vName == 'categories');
        JHtmlSidebar::addEntry(JText::_('COM_JVOTER_TITLE_CONTESTS'), 'index.php?option=com_jvoter&view=contests', $vName == 'contests');
        JHtmlSidebar::addEntry(JText::_('COM_JVOTER_TITLE_ENTRIES'), 'index.php?option=com_jvoter&view=entries', $vName == 'entries');
        JHtmlSidebar::addEntry(JText::_('COM_JVOTER_TITLE_PLANS'), 'index.php?option=com_jvoter&view=plans', $vName == 'plans');
        JHtmlSidebar::addEntry(JText::_('COM_JVOTER_TITLE_FEATURES'), 'index.php?option=com_jvoter&view=features', $vName == 'features');
    }
    
    /**
     * Gets a list of the actions that can be performed.
     *
     * @param   string   $component  The component name.
     * @param   string   $section    The access section name.
     * @param   integer  $id         The item ID.
     *
     * @return  \JObject
     *
     * @since   3.2
     */
    public static function getActions($section = '', $id = 0)
    {
        $assetName = 'com_jvoter';
        
        if ($section && $id)
        {
            $assetName .= '.' . $section . '.' . (int) $id;
        }
        
        $result = new \JObject;
        
        $user = Factory::getUser();
        
        $actions = Access::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/com_jvoter/access.xml', '/access/section[@name="component"]/'
            );
        
        if ($actions === false)
        {
            Log::add(
                \JText::sprintf('JLIB_ERROR_COMPONENTS_ACL_CONFIGURATION_FILE_MISSING_OR_IMPROPERLY_STRUCTURED', 'com_jvoter'), Log::ERROR, 'jerror'
                );
            
            return $result;
        }
        
        foreach ($actions as $action)
        {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }
        
        return $result;
    }
    
    /**
     * Format amount
     *
     * @params string   - the amount/price to be formatted
     *
     * @return string
     */
    public static function formatAmount($amount)
    {
        $params = ComponentHelper::getParams('com_jvoter');
        
        $num_decimals = $params->get('num_decimals', 2);
        $decimals_sep = $params->get('decimals_sep', '.');
        $thousands_sep = $params->get('thousands_sep');
        
        $amount = number_format($amount, $num_decimals, $decimals_sep, $thousands_sep);
        
        $currency_format = $params->get('currency_format');
        $amount = str_replace('[AMOUNT]', $amount, $currency_format);
        
        $currency = $params->get('currency');
        $amount = str_replace('[CURRENCY]', $currency, $amount);
        
        $currency_symbol = $params->get('currency_symbol');
        $amount = str_replace('[SYMBOL]', $currency_symbol, $amount);
        
        return $amount;
    }
    
    /**
     * Only returns plugins that have a specific event
     *
     * @param
     *            $eventName
     * @param
     *            $folder
     * @return array of JTable objects
     */
    public static function getPluginsWithEvent($eventName, $folder = 'otbpayment')
    {
        $return = array();
        if($plugins = self::getPlugins($folder))
        {
            foreach($plugins as $plugin)
            {
                if(self::hasEvent($plugin, $eventName))
                {
                    $return[] = $plugin;
                }
            }
        }
        return $return;
    }
    
    /**
     * Returns Array of active Plugins
     *
     * @param
     *            mixed Boolean
     * @param
     *            mixed Boolean
     * @return array
     */
    public static function getPlugins($folder = 'otbpayment')
    {
        $db = \JFactory::getDBO();
        $query = $db->getQuery(true)
        ->from($db->qn('#__extensions'))
        ->where($db->qn('enabled') . ' = ' . $db->q('1'))
        ->where(LOWER($db->qn('folder')) . ' = ' . $db->q(strtolower($folder)));
        $db->setQuery($query);
        
        $db->setQuery($query);
        return $db->loadObjectList();
    }
    
    /**
     * Checks if a plugin has an event
     *
     * @param obj $element
     *            the plugin JTable object
     * @param string $eventName
     *            the name of the event to test for
     * @return unknown_type
     */
    public static function hasEvent($element, $eventName)
    {
        $success = false;
        if(! $element || ! is_object($element))
        {
            return $success;
        }
        
        if(! $eventName || ! is_string($eventName))
        {
            return $success;
        }
        
        JPluginHelper::importPlugin('otbpayment', $element->element);
        $results = \JFactory::getApplication()->triggerEvent($eventName, array(
            $element
        ));
        
        if(in_array(true, $result, true))
        {
            $success = true;
        }
        
        return $success;
    }
    
    /**
     * Gets a list of payment plugins and their titles
     *
     * @return array
     */
    public static function getPaymentPlugins()
    {
        JLoader::import('joomla.plugin.helper');
        JPluginHelper::importPlugin('otbpayment');
        $app = \JFactory::getApplication();
        $results = $app->triggerEvent('onOTBPaymentGetIdentity');
        $return = array();
        foreach($results as $result)
        {
            if(is_object($result))
            {
                $return[] = $result;
            } elseif(is_array($result))
            {
                if(array_key_exists('name', $result))
                {
                    $return[] = (object) $result;
                } else
                {
                    foreach($result as $anItem)
                    {
                        if(is_object($anItem))
                        {
                            $return[] = $anItem;
                        } else
                        {
                            $return[] = (object) $anItem;
                        }
                    }
                }
            }
        }
        
        return $return; // name, title
    }
    
    /**
     * Truncate string
     *
     * @param string    $text
     * @param int       $length
     * @param string    $suffix
     * @return string
     */
    public function truncateString( $text, $length = '200', $suffix = 'Show more' )
    {
        if ( empty( $text ) )
        {
            return $text;
        }
        
        $allowed_tags = "";
        
        $text = self::stripArgumentFromTags( $text );
        $text = strip_tags( $text, $allowed_tags );
        $strlen = strlen( $text );
        
        if ( $length >= $strlen )
        {
            $length = $strlen;
        }
        
        $int = strpos( $text, ' ', $length );
        
        if ( $int < $length )
        {
            $int = $length;
        }
        
        $hideText = substr( $text, $int);
        $text = substr( $text, 0, $int );
        
        if ( !empty( $text ) && ($strlen > $int) )
        {
            $text .= '<span class="hidden-truncate" style="display: none;">';
            $text .= $hideText;
            $text .= '</span>';
            $text .= ' ' . $suffix;
        }
        
        return $text;
    }
    
    /**
     * Strip arguments from tags
     *
     * @param string $htmlString
     * @return string
     */
    public function stripArgumentFromTags( $htmlString )
    {
        $regEx = '/([^<]*<\s*[a-z](?:[0-9]|[a-z]{0,9}))(?:(?:\s*[a-z\-]{2,14}\s*=\s*(?:"[^"]*"|\'[^\']*\'))*)(\s*\/?>[^<]*)/i';
        // match any start tag
        $chunks = preg_split( $regEx, $htmlString, -1, PREG_SPLIT_DELIM_CAPTURE );
        $chunkCount = count( $chunks );
        $strippedString = '';
        for ( $n = 0; $n < $chunkCount; $n++ )
        {
            $strippedString .= $chunks[$n];
        }
        return $strippedString;
    }
}
