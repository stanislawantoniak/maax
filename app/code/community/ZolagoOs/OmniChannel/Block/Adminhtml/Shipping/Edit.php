<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Shipping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'udropship';
        $this->_controller = 'adminhtml_shipping';

        $this->_updateButton('save', 'label', Mage::helper('udropship')->__('Save Shipping Method'));
        $this->_updateButton('delete', 'label', Mage::helper('udropship')->__('Delete Shipping Method'));

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $model = Mage::getModel('udropship/shipping')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('shipping_data', $model);
        }
    }

    public function getHeaderText()
    {
        if( Mage::registry('shipping_data') && Mage::registry('shipping_data')->getId() ) {
            $data = Mage::registry('shipping_data');
            return Mage::helper('udropship')->__("Edit Method '%s'", $this->htmlEscape($data->getShippingCode()));
        } else {
            return Mage::helper('udropship')->__('New Method');
        }
    }
}
