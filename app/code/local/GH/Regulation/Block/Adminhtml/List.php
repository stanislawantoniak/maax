<?php
class GH_Regulation_Block_Adminhtml_List extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('ghregulation')->__('Add new vendor document'),
            'onclick' => "setLocation('{$this->getUrl('*/*/newItem')}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }

}