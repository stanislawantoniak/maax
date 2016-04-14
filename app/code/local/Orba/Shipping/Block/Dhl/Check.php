<?php
/**
 * check dhl connection button renderer
 */
class Orba_Shipping_Block_Dhl_Check
    extends Varien_Data_Form_Element_Abstract {

    public function getElementHtml() {
        return '<button type="button" id="dhl_check_button" class="btn btn-primary">'.
            Mage::helper('zolagodropship')->__("Check DHL Settings").
            '</button> <span id="dhl_check_message"></span>';        

    }

}