<?php

class Zolago_Dropship_Model_Source extends Unirgy_Dropship_Model_Source
{
    const VENDOR_TYPE_BRANDSHOP = 2;
    const VENDOR_TYPE_STANDARD = 1;

    const TRACK_STATUS_UNDELIVERED = 'U';

	public function toOptionHash($selector=false){
	    switch ($this->getPath()) {
	        case 'allvendorswithempty':
	            $out = $this->_getAllVendorsWithEmpty();
	            break;
            case 'vendorstype':
                $out = $this->_getVendorsType();
                break;
            case 'review_status':
                /** @var Zolago_Catalog_Model_Product_Source_Description $descriptionStatusSrc */
                $descriptionStatusSrc = Mage::getSingleton("zolagocatalog/product_source_description");
                $out = $descriptionStatusSrc->toOptionHash($selector);
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
            self::VENDOR_TYPE_STANDARD => $helper->__('Regular vendor'),
            self::VENDOR_TYPE_BRANDSHOP => $helper->__('Brandshop'),
        );
        return $out;
    }

    /**
     * @param bool $canAsk
     * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
     */
    public function getCanAskVendors($canAsk = true)
    {
        /** @var Zolago_Dropship_Model_Vendor $modelUdv */
        $modelUdv = Mage::getModel('udropship/vendor');
        /** @var Unirgy_Dropship_Model_Mysql4_Vendor_Collection $vendors */
        $vendors = $modelUdv->getCollection()
            ->setItemObjectClass('Varien_Object')
            ->addFieldToSelect(array('vendor_name'))
            ->addStatusFilter('A')
            ->addFilter('can_ask', $canAsk)
            ->setOrder('vendor_name', 'asc');

        return $vendors;
    }
    /**
     * @return Unirgy_Dropship_Model_Mysql4_Vendor_Collection
     */
    public function getCanAskBrandshops()
    {
        /** @var Zolago_Dropship_Model_Vendor $modelUdv */
        $modelUdv = Mage::getModel('udropship/vendor');
        /** @var Unirgy_Dropship_Model_Mysql4_Vendor_Collection $vendors */
        $vendors = $modelUdv->getCollection()
            ->setItemObjectClass('Varien_Object')
            ->addFieldToSelect(array('vendor_name'))
            ->addStatusFilter('A')
            ->addFilter('vendor_type', self::VENDOR_TYPE_BRANDSHOP)
            ->setOrder('vendor_name', 'asc');

        return $vendors;
    }

}
 