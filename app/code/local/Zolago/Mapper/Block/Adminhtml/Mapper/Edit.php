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
				'label'     => Mage::helper('zolagomapper')->__('Run'),
				'onclick'   => 'mapperControl.run();',
				'class'     => 'go',
			), -1);
			$this->_addButton('queue', array(
				'label'     => Mage::helper('zolagomapper')->__('Add to queue'),
				'onclick'   => "mapperControl.saveAndQueue();",
				'class'     => 'go',
			), -1);
		}
		if(!$this->getAttributeSetId()){
			$this->_updateButton("save", "label", Mage::helper('zolagomapper')->__('Next'));
			$this->_updateButton("save", "class", "");
			$this->_updateButton("save", "onclick", "mapperControl.next();");
			$this->_removeButton("reset");
		}
		$this->setDataObject($this->getModel());
		return $ret;
    }
	
	public function getBackUrl() {
		if(Mage::app()->getRequest()->getParam("back")=="list"){
			return parent::getBackUrl();
		}
		if($this->getIsNew() && $this->getAttributeSetId()){
			return $this->getUrl("*/*/new");
		}
		return parent::getBackUrl();

	}
	
    public function getIsNew() {
        return !(int)$this->getModel()->getId();
    }
	
    public function getAttributeSetId() {
		if($this->getIsNew()){
			return Mage::app()->getRequest()->getParam("attribute_set_id");
		}
		return $this->getModel()->getAttributeSetId();
    }
    
    public function getHeaderText() {
        if (!$this->getIsNew()) {
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
