<?php
/**
 * @package    JVoter
 * @copyright  Copyright (C) 2019 JVoter. All rights reserved.
 * @license    GNU General Public License version 3, or later
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldFeatures extends JFormField
{

    /**
     * The form field type.
     *
     * @var string
     * @since 1.6
     */
    protected $type = 'features';

    /**
     * Method to get the field input markup.
     *
     * @return string The field input markup.
     *        
     * @since 1.6
     */
    protected function getInput()
    {
        $html = '';
        
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__jvoter_features')
            ->where('state = 1');
        $db->setQuery($query);        
        $features = $db->loadObjectList();
        
        $html .= "<table class=\"table table-striped table-hover\">";
            $html .= "<thead>";
                $html .= "<tr>";
                    $html .= "<th width=\"1%\" class=\"center\">" . JHtml::_('grid.checkall') . "</th>";
                    $html .= "<th style=\"min-width:150px\" class=\"nowrap\">" . JText::_('JGLOBAL_TITLE') . "</th>";
                    $html .= "<th>" . JText::_('COM_JVOTER_FIELD_LABEL_LABEL') . "</th>";
                    $html .= "<th>" . JText::_('COM_JVOTER_FIELD_VALUE_LABEL') . "</th>";
                    $html .= "<th></th>";
                $html .= "</tr>";
            $html .= "</thead>";
            $html .= "<tbody>";
            foreach($features as $i => $feature)
            {               
                $html .= "<tr class=\"row" . $i % 2 . "\">";
                    $html .= "<td class=\"center\">" . JHtml::_('grid.id', $i, $feature->id) . "</td>";
                    $html .= "<td>{$feature->title}</td>";
                    $html .= "<td><input type=\"text\" value=\"{$feature->label}\"/></td>";
                    $html .= "<td>{$this->renderValue($feature)}</td>";
                    $html .= "<td><span class=\"btn btn-info hasPopover\" title=\"{$feature->title}\" data-placement=\"top\" data-content=\"".htmlspecialchars($feature->description)."\">" . JText::_('JGLOBAL_DESCRIPTION') . "</span></td>";
                $html .= "</tr>";
            }          
            $html .= "</tbody>";
        
        $html .= "</table>";
        
        return $html;
    }
    
    /**
     * Method to render appropriate display for a particular feature type
     * 
     * @params object   - feature object
     */
    private function renderValue($feature)
    {        
        $html = '';
        switch($feature->type)
        {
            case 'boolean':                
                $html .= '
                <fieldset id="jform_show_jed_info" class="btn-group btn-group-yesno radio">
					<input type="radio" id="jform_show_jed_info0" name="jform[show_jed_info]" value="1" checked="checked">			
                    <label for="jform_show_jed_info0" class="btn active btn-success">Yes</label>
					<input type="radio" id="jform_show_jed_info1" name="jform[show_jed_info]" value="0">			
                    <label for="jform_show_jed_info1" class="btn">No</label>
			     </fieldset>';
                break;
            
            case 'integer':           
                $html .= '<input type="number" name="'.$feature->namekey.'" value="'.$feature->value.'" />';
                break;
            case 'text':
            default:
                $html .= '<input type="text" name="'.$feature->namekey.'" value="'.$feature->value.'" />';
                break;
        }
        
        return $html;
    }
}
