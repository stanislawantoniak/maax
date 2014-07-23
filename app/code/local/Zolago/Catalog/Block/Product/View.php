<?php
class Zolago_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_View
{
	/**
	 * @return Zolago_Dropship_Model_Vendor
	 */
    public function getVendor() {
		if(!$this->getData('vendor')){
			$vendor = Mage::helper('udropship')->getVendor($this->getProduct()->getUdropshipVendor());
			$this->setData('vendor', $vendor);
		}
		return $this->getData('vendor');
	}
	
	/**
	 * @param Zolago_Dropship_Model_Vendor|null $vendor
	 * @return string
	 */
	public function getStoreDeliveryHeadline(Zolago_Dropship_Model_Vendor $vendor=null) {
		if(is_null($vendor)){
			$vendor = $this->getVendor();
		}
		return $vendor->getStoreDeliveryHeadline();
	}
	
	/**
	 * @param Zolago_Dropship_Model_Vendor|null $vendor
	 * @return string
	 */
	public function getStoreReturnHeadline(Zolago_Dropship_Model_Vendor $vendor=null) {
		if(is_null($vendor)){
			$vendor = $this->getVendor();
		}
		return $vendor->getStoreReturnHeadline();
	}

    /**
     * @todo Implementation
     *
     * @return string
     */
    public function getParentCategoryName()
    {
        return 'Sukienki';
    }

    /**
     * @todo Implementation
     *
     * @return string
     */
    public function getParentCategoryUrl()
    {
        return '/dla-niej.html';
    }

    /**
     * @todo Implementation
     *
     * @return mixed
     */
    public function getProductFlagLabel()
    {
        $flags = array('', 'new', 'hit', 'percent', 'sale');
        return $flags[rand(0, 4)];
    }
}
