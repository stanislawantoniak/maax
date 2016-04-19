<?php

class Zolago_Dropship_Model_Source extends ZolagoOs_OmniChannel_Model_Source
{
    const VENDOR_TYPE_BRANDSHOP = 2;
    const VENDOR_TYPE_STANDARD = 1;

    const TRACK_STATUS_UNDELIVERED = 'U';
	const TRACK_UNDELIVERED_SUFFIX = '_UNDELIVERED';


    const BRANDSHOP_INDEX_BY_GOOGLE_USE_VENDOR_CONFIG = 0;
    const BRANDSHOP_INDEX_BY_GOOGLE_YES = 1;
    const BRANDSHOP_INDEX_BY_GOOGLE_NO = 2;

	protected $_allvendors = array();

	public function toOptionHash($selector=false){
	    switch ($this->getPath()) {
	        case 'allvendorswithempty':
	            $out = $this->_getAllVendorsWithEmpty();
	            break;
	        case 'allvendorswithdisabled':
	            $out = $this->_getAllVendorsWithDisabled();
	            break;
            case 'vendorstype':
                $out = $this->_getVendorsType();
                break;
            case 'vendorindexbygoogle':
                $out = $this->_getVendorIndexByGoogle();
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
   
   
    protected function _getAllVendorsWithDisabled()
    {
        $field = 'vendor_name';
        if (empty($this->_allvendors)) {
            $vendors = Mage::getModel('udropship/vendor')->getCollection()
                ->setItemObjectClass('Varien_Object')
                ->addFieldToSelect(array($field))
                ->setOrder('vendor_name', 'asc');
            foreach ($vendors as $v) {
                $this->_allvendors[$v->getVendorId()] = $v->getDataUsingMethod($field);
            }
        }
        return $this->_allvendors;
    }

    /**
     * 
     * @return array
     */
    protected function _getAllVendorsWithEmpty() {
			$out = $this->getVendors();
			$out = array_reverse($out, true);
			$out[""] = "";
			return array_reverse($out, true);
    }

    /**
     * @return array
     */
    protected function _getVendorIndexByGoogle()
    {
        $helper = Mage::helper('zolagodropship');
        $indexByGoogleOptions = array(
            self::BRANDSHOP_INDEX_BY_GOOGLE_USE_VENDOR_CONFIG => $helper->__('Use vendor config'),
            self::BRANDSHOP_INDEX_BY_GOOGLE_YES => $helper->__('Yes'),
            self::BRANDSHOP_INDEX_BY_GOOGLE_NO => $helper->__('No'),
        );
        return $indexByGoogleOptions;
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
     * @return ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection
     */
    public function getCanAskVendors($canAsk = true)
    {
        /** @var Zolago_Dropship_Model_Vendor $modelUdv */
        $modelUdv = Mage::getModel('udropship/vendor');
        /** @var ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection $vendors */
        $vendors = $modelUdv->getCollection()
            ->setItemObjectClass('Varien_Object')
            ->addFieldToSelect(array('vendor_name'))
            ->addStatusFilter('A')
            ->addFilter('can_ask', $canAsk)
            ->setOrder('vendor_name', 'asc');

        return $vendors;
    }
    /**
     * @return ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection
     */
    public function getCanAskBrandshops()
    {
        /** @var Zolago_Dropship_Model_Vendor $modelUdv */
        $modelUdv = Mage::getModel('udropship/vendor');
        /** @var ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection $vendors */
        $vendors = $modelUdv->getCollection()
            ->setItemObjectClass('Varien_Object')
            ->addFieldToSelect(array('vendor_name'))
            ->addStatusFilter('A')
            ->addFilter('vendor_type', self::VENDOR_TYPE_BRANDSHOP)
            ->setOrder('vendor_name', 'asc');

        return $vendors;
    }

}
 