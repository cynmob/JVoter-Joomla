<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldContest extends JFormField
{

    /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
    protected $type = 'contest';

    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup.
     *        
     * @since 1.6
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $attr = '';
        $attr .= ! empty($this->class) ? ' class="' . $this->class . '"' : '';
        $attr .= ! empty($this->size) ? ' size="' . $this->size . '"' : '';
        $attr .= $this->multiple ? ' multiple' : '';
        $attr .= $this->required ? ' required aria-required="true"' : '';
        $attr .= $this->autofocus ? ' autofocus' : '';
        
        // To avoid user's confusion, readonly="true" should imply
        // disabled="true".
        if((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1' || (string) $this->disabled == 'true')
        {
            $attr .= ' disabled="disabled"';
        }
        
        // Initialize JavaScript field attributes.
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
                
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__jvoter_contests')
            ->where('state = 1');
        
        $db->setQuery($query);
        
        $contests = $db->loadObjectList();
            
        $options = array();
        
        if(! $this->multiple)
        {
            $options[] = JHtml::_('select.option', "", JText::_('COM_JVOTER_SELECT_CONTEST_OPT_LABEL'));
        }
        
        if(is_array($contests))
        {
            foreach($contests as $contest)
            {                                              
                $options[] = JHtml::_('select.option', $contest->id, $contest->title);
            }
        }
       
        return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
    }
}
