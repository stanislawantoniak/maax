<?php
class GH_Regulation_Block_Adminhtml_List extends GH_Regulation_Block_Adminhtml_Abstract {
 
    protected function _prepareLayout() {
        $this->setData('button_label', Mage::helper('ghregulation')->__('Add new document'));
        $this->setData('button_url', '*/*/newDocument');
        return parent::_prepareLayout();
    }

}