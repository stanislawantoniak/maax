<?php
class Zolago_Pos_Block_Adminhtml_Pos extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('zolagopos')->__('Create POS'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }
    
}