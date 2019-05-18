<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * Fields Type
 *
 */
class JFormFieldType extends JFormFieldList
{
	public $type = 'Type';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.7.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		$this->onchange = 'typeHasChanged(this);';

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		$options[] = JHtml::_('select.option', 'boolean', JText::_('COM_JVOTER_FEATURE_FILTER_TYPE_OPT_BOOLEAN'));
		$options[] = JHtml::_('select.option', 'integer', JText::_('COM_JVOTER_FEATURE_FILTER_TYPE_OPT_INTEGER'));
		$options[] = JHtml::_('select.option', 'text', JText::_('COM_JVOTER_FEATURE_FILTER_TYPE_OPT_TEXT'));

		// Sorting the fields based on the text which is displayed
		usort(
			$options,
			function ($a, $b)
			{
				return strcmp($a->text, $b->text);
			}
		);

		JFactory::getDocument()->addScriptDeclaration("
			jQuery( document ).ready(function() {
				Joomla.loadingLayer('load');
			});
			function typeHasChanged(element){
				Joomla.loadingLayer('show');
				var cat = jQuery(element);
				jQuery('input[name=task]').val('feature.reload');
				element.form.submit();
			}
		"
		);

		return $options;
	}
}
