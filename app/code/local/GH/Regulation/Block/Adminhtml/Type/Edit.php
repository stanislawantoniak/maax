<?php

/**
 * Class Gh_Regulation_Block_Adminhtml_Type_Edit
 */
class Gh_Regulation_Block_Adminhtml_Type_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * @return GH_Regulation_Model_Regulation_Type
     */
    public function getModel() {
        return Mage::registry('ghregulation_current_type');
    }

	public function __construct() {
        $this->_objectId = 'regulation_type_id';
        $this->_blockGroup = 'ghregulation';
        $this->_controller = 'adminhtml_regulation';
		parent::__construct();
	}
	
	public function getBackUrl() {
        return $this->getUrl("*/*/type");
	}
	
    public function getIsNew() {
        return !(int)$this->getModel()->getId();
    }
	    
    public function getHeaderText() {
        if (!$this->getIsNew()) {
            return Mage::helper('ghregulation')->__('Edit document type');
        }
        return  Mage::helper('ghregulation')->__('New document type');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/saveType', array("_current" => true));
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/deleteType', array("_current" => true));
    }

}
