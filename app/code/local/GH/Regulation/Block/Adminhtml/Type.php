<?php
class GH_Regulation_Block_Adminhtml_Type extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('ghregulation')->__('Create new type of document'),
            'onclick' => "setLocation('{$this->getUrl('*/*/newType')}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }

}