<?php

class Zolago_Dropship_Model_Source extends Unirgy_Dropship_Model_Source
{
    const VENDOR_TYPE_BRANDSHOP = 2;
    const VENDOR_TYPE_STANDARD = 1;
	public function toOptionHash($selector=false){
	    switch ($this->getPath()) {
	        case 'allvendorswithempty':
	            $out = $this->_getAllVendorsWithEmpty();
	            break;
            case 'vendorstype':
                $out = $this->_getVendorsType();
                break;
            default:
                $out = parent::toOptionHash($selector);
	    }
	    return $out;
   }
   
    /**
     * 
     * @return array
     */
    protected function _getAllVendorsWithEmpty() {
			$out = $this->getVendors();
			$out = array_reverse($out, true);
			$out[""] = "";
			return array_reverse($out, true);;
    }
    
    /**
     * hardcoded vendors type
     * @return array
     */
    protected function _getVendorsType() {
        $helper = Mage::helper('zolagodropship');
        $out = array (
            self::VENDOR_TYPE_STANDARD => $helper->__('Standard'),
            self::VENDOR_TYPE_BRANDSHOP => $helper->__('Brandshop'),
        );
        return $out;
    }

}
 