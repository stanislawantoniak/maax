<?php
class Zolago_Holidays_Block_Adminhtml_Holidays_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct(){
		$this->_objectId   = 'holiday_id';  
        $this->_blockGroup = 'zolagoholidays';
        $this->_controller = 'adminhtml_holidays';
     
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
        if (Mage::registry('holiday')->getHolidayId()) {
            return $this->__('Edit Holiday');
        }  
        else {
            return $this->__('New Holiday');
        }  
    } 
}

