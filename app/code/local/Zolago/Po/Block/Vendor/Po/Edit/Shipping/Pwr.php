<?php
class Zolago_Po_Block_Vendor_Po_Edit_Shipping_Pwr
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
	implements Zolago_Po_Block_Vendor_Po_Edit_Shipping_Interface
{
    protected function _construct() {
        $this->setTemplate('zolagopo/vendor/po/edit/shipping/pwr.phtml');
        return parent::_construct();
    }

	
	public function getFormUrl() {
		return  $this->getPoUrl("saveShipping", array("mode"=>$this->getMode()));
	}
	
	public function getMode() {
		return self::MODE_GENERATE;
	}
    public function getShippingMethod() {
        return Orba_Shipping_Model_Packstation_Pwr::CODE;
    }	
	
}
