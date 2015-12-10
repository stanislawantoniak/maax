<?php
/**
 * sql querys grid header
 */

class GH_Common_Block_Adminhtml_Rawsql extends Mage_Adminhtml_Block_Widget_Container {

    
    /**
     * new button "Add query"
     */

    protected function _prepareLayout() {
        $this->setData('button_label', Mage::helper('ghcommon')->__('Add new query'));
        $this->setData('button_url', '*/*/edit');
        $this->_addButton('add_new', array(
                              'label'   => $this->getData('button_label'),
                              'onclick' => "setLocation('{$this->getUrl($this->getData('button_url'))}')",
                              'class'   => 'add'
                          ));
        return parent::_prepareLayout();
    }

}