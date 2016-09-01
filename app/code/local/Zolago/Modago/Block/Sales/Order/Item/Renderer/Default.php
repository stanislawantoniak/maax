<?php
/**
 * default order view renderer
 */
class Zolago_Modago_Block_Sales_Order_Item_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default {

	/**
	 * 
	 * @param string $imageUrl
	 * @return string
	 */
	public function getBase64Content($imageUrl) {
		$ret = Mage::helper("zolagocommon")->getFileBase64ByUrl(
			$imageUrl, 
			$this->getItem()->getPo()->getStoreId()
		);
		
		if($ret!=""){
			$ret = "data:image/jpeg;base64,".$ret;
		}else{
			$ret = $imageUrl;
		}
		
		return $ret;
	}
    //{{{ 
    /**
     * product
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct() {
        return $this->getItem()->getOrderItem()->getProduct();
    }
    //}}}
    //{{{
    /**
     * product name
     * @return string
     */
    public function getProductName() {
        $product = $this->getItem();
        return $product->getData('name');
    }
    //}}}
    //{{{
    /**
     * product thumbnail
     * @return string
     */
    public function getProductThumbnail() {
        $product = $this->getItem()->getOrderItem()->getProduct();
        return Mage::helper('catalog/image')->init($product, 'thumbnail');
    }
    //}}}

    /**
     * Get list of all otions for product
     *
     * @return array
     */
    public function getOptionList()
    {
        $item = $this->getItem()->getOrderItem();
        $options = $item->getProductOptions();
        return isset($options['attributes_info']) ? $options['attributes_info']:array();
    }

}