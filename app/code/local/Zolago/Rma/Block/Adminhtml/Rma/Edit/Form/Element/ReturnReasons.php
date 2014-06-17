<?php
class Zolago_Rma_Block_Adminhtml_Rma_Edit_Form_Element_ReturnReasons extends Varien_Data_Form_Element_Abstract
{
	public function getHtml()
    {
        $this->_renderer = Mage::getSingleton('core/layout')->createBlock('zolagorma/adminhtml_rma_edit_renderer_returnreasons');
        return parent::getHtml();
    }
}