<?php
class Zolago_Mapper_Block_Adminhtml_Mapper_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

	
	/**
     *  @return Zolago_Mapper_Model_Mapper
     */
    public function getModel() {
        return Mage::registry('zolagomapper_current_mapper');
    }

	
	public function __construct()
    {
        $this->_objectId = 'mapper_id';
        $this->_blockGroup = 'zolagomapper';
        $this->_controller = 'adminhtml_mapper';
				
		parent::__construct();
		

	}
	
    protected function _prepareLayout() {
        $ret = parent::_prepareLayout();
		if(!$this->getIsNew()){
			$this->_addButton('run', array(
				'label'     => Mage::helper('adminhtml')->__('Run'),
				'onclick'   => 'setLocation(\'' .Mage::getUrl("*/*/run", array("mapper_id"=>$this->getModel()->getId())) . '\')',
				'class'     => 'go',
			), -1);
		}
		$this->setDataObject($this->getModel());
		return $ret;
    }
	
    public function getIsNew() {
        return !(int)$this->getModel()->getId();
    }
    
    public function getHeaderText() {
        if ($this->getIsNew()) {
            return Mage::helper('zolagomapper')->__('Edit mapper');
        }
        return  Mage::helper('zolagomapper')->__('New mapper');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/save', array("_current"=>true));
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/delete', array("_current"=>true));
    }

}
