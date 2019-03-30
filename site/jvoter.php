<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('JVoter', JPATH_COMPONENT);
JLoader::register('JVoterHelperSite', JPATH_COMPONENT . '/helpers/jvoter.php');
JLoader::register('JVoterController', JPATH_COMPONENT . '/controller.php');
JLoader::register('JVoterHelper', JPATH_COMPONENT_ADMINISTRATOR . '/helpers/jvoter.php');

$doc = JFactory::getDocument();
$uri = JURI::getInstance();
$js = "var com_jvoter = {};\n";
$js .= "com_jvoter.jbase = '" . $uri->root() . "';\n";
$doc->addScriptDeclaration($js);

// Execute the task.
$controller = JControllerLegacy::getInstance('JVoter');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
