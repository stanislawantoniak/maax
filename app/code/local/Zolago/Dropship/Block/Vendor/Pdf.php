<?php
class Zolago_Dropship_Block_Vendor_Pdf 
    extends Mage_Core_Block_Template {

    protected $_vendor;
    protected $_helper;
    
    public function getHelper() {
        if (empty($this->_helper)) {
            $this->_helper = Mage::helper('zolagodropship');
        }
        return $this->_helper;
    }
    public function setVendorId($id) {
        $this->_vendor = Mage::getModel('udropship/vendor')->load($id);        
        return $this;
    }
    
    public function getVendor() {
        return $this->_vendor;
    }

    protected function _getItem($name,$value) {
        $hlp = $this->getHelper();
        $item = array(
            'field_name' => $hlp->__($name),
            'field_value' => $this->getVendor()->getData($value),
        );
        return $item;
    }
    
    public function getVendorData() {
        $hlp = Mage::helper('udropship');
        $out = array();
        $out[] = $this->_getItem('Company name','company_name');
        $out[] = $this->_getItem('Street','street');
        $out[] = $this->_getItem('City','city');
        $out[] = $this->_getItem('Zip / Postal code','zip');
        $out[] = $this->_getItem('NIP','tax_no');
        $yesNo = array(''=>'',0 => Mage::helper('zolagocommon')->__("No"), 1 => Mage::helper('zolagocommon')->__("Yes"));
        $item = $this->_getItem('Are regulations accepted','regulation_accepted');
        $item['field_value'] = $yesNo[$item['field_value']];
        $out[] = $item;
        $out[] = $this->_getItem('Confirmation request send date','regulation_confirm_request_sent_date');
        $out[] = $this->_getItem('Acceptation date','regulation_accept_document_date');
        $json = $this->getVendor()->getData('regulation_accept_document_data');
        $block = Mage::getSingleton('core/layout')->createBlock("core/template");
        $block->setTemplate("zolagodropship/vendor/helper/form/regulation.phtml");
        $block->setValue($json);
        $customHtml = $block->toHtml();;
        $out[] = array (
            'field_name' => $this->getHelper()->__('Acceptation details'),
            'field_value' => $customHtml,
        );

        return $out;
    }
    
}