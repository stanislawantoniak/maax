<?php
/**
 * Description of Title
 */
class Zolago_Solrsearch_Block_Catalog_Product_List_Header_Category 
	extends Zolago_Solrsearch_Block_Catalog_Product_List_Header_Abstract {
	
	protected function _construct(){
		$this->setTemplate('zolagosolrsearch/catalog/product/list/header/category.phtml');
	}
    /**
     * @param Unirgy_Dropship_Model_Vendor $vendor
     * @return string | null
     */
    public function getVendorResizedLogoUrl(Unirgy_Dropship_Model_Vendor $vendor,
                                            $width=163, $height=52) {
        /* @var $zolagodropship Zolago_Dropship_Helper_Data */
        $zolagodropship = Mage::helper("zolagodropship");

        return $zolagodropship->getVendorLogoResizedUrl($vendor, $width, $height);
    }
	
	
}