<?php
class GH_Dhl_Block_Adminhtml_Dhl extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('ghdhl')->__('Add DHL Account'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }
    
}