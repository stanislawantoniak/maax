<?php
/**
 * edit form - vendor brandshop settings
 */

class Zolago_Dropship_Block_Adminhtml_Vendor_Brandshop_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    protected $_blockGroup = 'zolagodropship';
    public function __construct()
    {
        parent::__construct();
        
        $request = $this->getRequest();
        $vendorId = $request->getParam('vendor_id');
        $brandshopId = $request->getParam('brandshop_id');

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_vendor_brandshop';
        $this->_updateButton('save', 'label', Mage::helper('zolagodropship')->__('Save settings'));        
        $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('zolagoosadmin/adminhtml_vendor/edit/',array('id'=>$vendorId,'active_tab' => 'brandshop_section')).'\')' );
        $this->_updateButton('save', 'id', 'save_button');
        $this->_removeButton('reset');
        

        $model =  Mage::getModel('zolagodropship/vendor_brandshop');
        $model->loadByVendorBrandshop($vendorId,$brandshopId);
        Mage::register('vendor_brandshop',$model);
    }

    public function getHeaderText()
    {
        return Mage::helper('zolagodropship')->__('Brandshop settings');
    }

}