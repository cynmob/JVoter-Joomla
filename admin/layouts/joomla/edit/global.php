<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

$app       = JFactory::getApplication();
$form      = $displayData->getForm();
$input     = $app->input;
$component = $input->getCmd('option', 'com_jvoter');

if ($component === 'com_categories')
{
	$extension = $input->getCmd('extension', 'com_jvoter');
	$parts     = explode('.', $extension);
	$component = $parts[0];
}

$saveHistory = JComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('fields') ?: array(
	array('parent', 'parent_id'),
	array('published', 'state', 'enabled'),
	array('category', 'catid'),
    'planid',
	'featured',
	'sticky',
	'access',
	'id',
	'language',
	'tags',
	'note',
	'version_note',
);

$hiddenFields   = $displayData->get('hidden_fields') ?: array();
$hiddenFields[] = 'id';

if (!$saveHistory)
{
	$hiddenFields[] = 'version_note';
}

$html   = array();
$html[] = '<fieldset class="form-vertical">';

foreach ($fields as $field)
{
	foreach ((array) $field as $f)
	{
		if ($form->getField($f))
		{
			if (in_array($f, $hiddenFields))
			{
				$form->setFieldAttribute($f, 'type', 'hidden');
			}

			$html[] = $form->renderField($f);
			break;
		}
	}
}

$html[] = '</fieldset>';

echo implode('', $html);
