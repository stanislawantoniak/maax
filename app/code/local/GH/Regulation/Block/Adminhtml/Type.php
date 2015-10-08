<?php
class GH_Regulation_Block_Adminhtml_Type extends GH_Regulation_Block_Adminhtml_Abstract {
 
    protected function _prepareLayout() {
        $this->setData('button_label', Mage::helper('ghregulation')->__('Create new type of document'));
        $this->setData('button_url', '*/*/newType');
        return parent::_prepareLayout();
    }

}