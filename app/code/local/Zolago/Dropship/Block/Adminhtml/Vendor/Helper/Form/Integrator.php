<?php

/**
 * Class Zolago_Dropship_Block_Adminhtml_Vendor_Helper_Form_Regulation
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Helper_Form_Integrator
    extends Varien_Data_Form_Element_Abstract
{

    public function getElementHtml()
    {
        $block = Mage::getSingleton('core/layout')->createBlock("core/template");
        $block->setTemplate("zolagodropship/vendor/helper/form/integrator.phtml");
        $block->setIntegratorSecret(Mage::getModel('udropship/vendor')->load(Mage::app()->getRequest()->getParam('id'))->getIntegratorSecret());
        return $block->toHtml();
    }
}