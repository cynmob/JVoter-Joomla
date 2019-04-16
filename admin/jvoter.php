<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

// Access check.
if(! JFactory::getUser()->authorise('core.manage', 'com_jvoter'))
{
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

$doc = JFactory::getDocument();
$uri = JURI::getInstance();
$js = "var com_jvoter = {};\n";
$js .= "com_jvoter.jbase = '" . $uri->root() . "';\n";
$doc->addScriptDeclaration($js);

JHtml::_('stylesheet', 'com_jvoter/font-awesome.min.css', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'com_jvoter/backend.min.css', array('version' => 'auto', 'relative' => true));

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::register('JVoterHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/jvoter.php');

$controller = JControllerLegacy::getInstance('JVoter');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();