<?php

class Varien_Data_Form_Element_Time extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {

        $this->addClass('select');

        $value_hrs = 0;
        $value_min = 0;
        $value_sec = 0;

        if( $value = $this->getValue() ) {
            $values = explode(',', $value);
            if( is_array($values) && count($values) == 3 ) {
                $value_hrs = $values[0];
                $value_min = $values[1];
                $value_sec = $values[2];
            }
        }

        $html = '<input type="hidden" id="' . $this->getHtmlId() . '" />';
        $html .= '<select name="'. $this->getName() . '" '.$this->serialize($this->getHtmlAttributes()).' style="width:40px">'."\n";
        for( $i=0;$i<24;$i++ ) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_hrs == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }
        $html.= '</select>'."\n";

        $html.= '&nbsp;:&nbsp;<select name="'. $this->getName() . '" '.$this->serialize($this->getHtmlAttributes()).' style="width:40px">'."\n";
        for( $i=0;$i<60;$i++ ) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_min == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }
        $html.= '</select>'."\n";

        $html.= '&nbsp;:&nbsp;<select name="'. $this->getName() . '" '.$this->serialize($this->getHtmlAttributes()).' style="width:40px">'."\n";
        for( $i=0;$i<60;$i++ ) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $html.= '<option value="'.$hour.'" '. ( ($value_sec == $i) ? 'selected="selected"' : '' ) .'>' . $hour . '</option>';
        }
        $html.= '</select>'."\n";
        $html.= $this->getAfterElementHtml();
        return $html;
    }
}