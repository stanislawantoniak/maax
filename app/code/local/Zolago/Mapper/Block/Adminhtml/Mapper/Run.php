<?php
class Zolago_Mapper_Block_Adminhtml_Mapper_Run extends Mage_Adminhtml_Block_Widget_Container {
 
    protected function _prepareLayout() {
        $mapper = Mage::registry('zolagomapper_current_mapper');
        $ret = parent::_prepareLayout();
			$this->_addButton('back', array(
			    'label'     => Mage::helper('zolagomapper')->__('Back'),
				'onclick'   => 'setLocation(\'' .$this->getUrl("*/*/edit", array("mapper_id"=>$mapper->getId())) . '\')',
				'class'     => 'back',
			), -1);

        return $ret;
    }
    
}