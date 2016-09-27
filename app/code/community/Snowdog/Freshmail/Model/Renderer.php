<?php

class Snowdog_Freshmail_Model_Renderer implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render popup form field row
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<label id="' . $element->getHtmlId() . '" class="freshmail-container__label">';
        $html .= htmlspecialchars($element->getLabel(), ENT_COMPAT);
        $html .= '</label>';
        $html .= '<div class="freshmail-container__field-container">';
        $html .= $element->setClass('freshmail-container__field')->getElementHtml();
        $html .= '</div>';

        return '<div class="freshmail-container__field-row">' . $html . '</div>';
    }
}
