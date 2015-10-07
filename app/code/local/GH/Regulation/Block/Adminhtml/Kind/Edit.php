<?php
class Gh_Regulation_Block_Adminhtml_Kind_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	/**
     *  @return Zolago_Mapper_Model_Mapper
     */
    public function getModel() {
        return Mage::registry('ghregulation_current_kind');
    }

	
	public function __construct()
    {
        $this->_objectId = 'regulation_kind_id';
        $this->_blockGroup = 'ghregulation';
        $this->_controller = 'adminhtml_regulation';
				
		parent::__construct();

	}
	
	public function getBackUrl() {
			return $this->getUrl("*/*/kind");
	}
	
    public function getIsNew() {
        return !(int)$this->getModel()->getId();
    }
	    
    public function getHeaderText() {
        if (!$this->getIsNew()) {
            return Mage::helper('ghregulation')->__('Edit document kind');
        }
        return  Mage::helper('ghregulation')->__('New document kind');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/saveKind', array("_current"=>true));
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/deleteKind', array("_current"=>true));
    }

}
