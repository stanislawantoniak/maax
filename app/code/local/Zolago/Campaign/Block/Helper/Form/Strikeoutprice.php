<?php

class Zolago_Campaign_Block_Helper_Form_Strikeoutprice extends Varien_Data_Form_Element_Select
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        return $html . "  <script>
                        $('" . $this->getHtmlId() . "').disable();
                        </script>";
    }
}