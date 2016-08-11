<?php
class GH_Regulation_Block_Adminhtml_Abstract extends Mage_Adminhtml_Block_Widget_Container {

    protected function _prepareLayout() {
        $this->_addButton('add_new', array(
            'label'   => $this->getData('button_label'),
            'onclick' => "setLocation('{$this->getUrl($this->getData('button_url'))}')",
            'class'   => 'add'
        ));
        return parent::_prepareLayout();
    }

}