<?php
class Zolago_Rma_Block_Adminhtml_Rma_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct(){
        $this->_objectId   = 'return_reason_id';
        $this->_blockGroup = 'zolagorma';
        $this->_controller = 'adminhtml_rma';
		$this->_mode       = 'edit';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText(){
        if (Mage::registry('returnreason')->getReturnReasonId()) {
            return $this->__('Edit Return Reason');
        }
        else {
            return $this->__('New Return Reason');
        }
    }
}