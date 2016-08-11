<?php
class Zolago_Catalog_Block_Product_Vendor_Abstract 
	extends Mage_Core_Block_Template
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
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getBrandshopVendor()
    {
        if (!$this->getData('brandshop_vendor')) {
            $vendor = Mage::helper('udropship')->getVendor($this->getProduct()->getData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_BRANDSHOP_CODE));
            $this->setData('brandshop_vendor', $vendor);
        }
        return $this->getData('brandshop_vendor');
    }

	/**
	 * @return Zolago_Catalog_Model_Product
	 */
	public function getProduct() {
		if(!$this->getData("product")){
			if($this->getParentBlock()) {
				$product = $this->getParentBlock()->getProduct();
			} else {
				$product = false;
			}
			if(!$product){
				$product = Mage::registry('current_product');
			}
			$this->setData("product", $product);
		}
		return $this->getData("product");
	}
	
}
