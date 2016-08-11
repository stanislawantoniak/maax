<?php
/**
 * edit form - vendor brandshop settings
 */

class GH_Regulation_Block_Adminhtml_Kind_Edit_Vendor extends Mage_Adminhtml_Block_Widget_Form_Container {
    protected $_blockGroup = 'ghregulation';
    public function __construct()
    {
        parent::__construct();
        
        $request = $this->getRequest();
        $vendorId = $request->get('id');
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_kind_vendor';
        $this->_updateButton('save', 'label', Mage::helper('zolagodropship')->__('Add document type'));        
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId,'active_tab' => 'regulation_type')).'\')' );
        $this->_updateButton('save', 'id', 'save_button');
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        

    }

    public function getHeaderText()
    {
        return Mage::helper('zolagodropship')->__('Kind document settings');
    }
}