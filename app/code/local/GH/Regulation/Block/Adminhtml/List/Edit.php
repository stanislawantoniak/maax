<?php

/**
 * Class Gh_Regulation_Block_Adminhtml_List_Edit
 */
class Gh_Regulation_Block_Adminhtml_List_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * @return GH_Regulation_Model_Regulation_Document
     */
    public function getModel() {
        return Mage::registry('ghregulation_current_document');
    }

	public function __construct() {
        $this->_objectId = 'id';
        $this->_blockGroup = 'ghregulation';
        $this->_controller = 'adminhtml_regulation';
		parent::__construct();
	}
	
	public function getBackUrl() {
        return $this->getUrl("*/*/list");
	}
	
    public function getIsNew() {
        return !(int)$this->getModel()->getId();
    }
	    
    public function getHeaderText() {
        if (!$this->getIsNew()) {
            return Mage::helper('ghregulation')->__('Edit document: %s', $this->getModel()->getFileName());
        }
        return  Mage::helper('ghregulation')->__('New document');
    }

    public function getSaveUrl() {
        return $this->getUrl('*/*/saveDocument', array("_current" => true));
    }

    public function getDeleteUrl() {
        return $this->getUrl('*/*/deleteDocument', array("_current" => true));
    }

}
