<?php
/**
 * builder for contact fieldset
 */
class Zolago_Pos_Helper_Form_Fieldset_Contact extends Zolago_Common_Helper_Form_Fieldset_Abstract
{
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }
}