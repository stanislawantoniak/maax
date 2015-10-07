<?php
class GH_Regulation_Block_Adminhtml_Kind extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('ghregulation')->__('Create new kind of document'),
            'onclick' => "setLocation('{$this->getUrl('*/*/newKind')}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }

}