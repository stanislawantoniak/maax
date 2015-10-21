<?php
class GH_Regulation_Block_Adminhtml_Kind extends GH_Regulation_Block_Adminhtml_Abstract {
 
    protected function _prepareLayout() {
        $this->setData('button_label', Mage::helper('ghregulation')->__('Create new kind of document'));
        $this->setData('button_url', '*/*/newKind');
        return parent::_prepareLayout();
    }

}