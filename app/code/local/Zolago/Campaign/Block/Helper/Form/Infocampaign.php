<?php

class Zolago_Campaign_Block_Helper_Form_Infocampaign extends Varien_Data_Form_Element_Multiselect
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        return $html . "  <script>
                        $('" . $this->getHtmlId() . "').disable();
                        </script>";
    }
}