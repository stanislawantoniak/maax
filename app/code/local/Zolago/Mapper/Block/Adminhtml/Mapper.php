<?php
class Zolago_Mapper_Block_Adminhtml_Mapper extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => Mage::helper('zolagomapper')->__('Create mapper'),
            'onclick' => "setLocation('{$this->getUrl('*/*/new')}')",
            'class'   => 'add'
        ));
        $this->_addButton('run_all', array(
            'label'   => Mage::helper('zolagomapper')->__('Run all'),
            'onclick' => "setLocation('{$this->getUrl('*/*/runAll')}')",
        ));
        return parent::_prepareLayout();
    }
    
}