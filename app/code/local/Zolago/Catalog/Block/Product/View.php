<?php
class Zolago_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_View
{
	/**
	 * @return bool
	 */
	public function getIsBrandshop() {
		if($this->getVendorContext()){
			return $this->getVendorContext()->isBrandshop();
		}
		return false;
	}
	
	/**
	 * @return string
	 */
	public function getVendorUrl() {
		return $this->getVendorContext()->getVendorUrl();
	}
	
	/**
	 * @return string
	 */
	public function getVendorName() {
		return $this->getVendorContext()->getVendorName();
	}
	
	/**
	 * @return string
	 */
	public function getVendorLogoUrl() {
		return Mage::getBaseUrl('media') . $this->getVendorContext()->getLogo();
	}
	
	/**
	 * @return Zolago_Dropship_Model_Vendor
	 */
    public function getVendorContext() {
		return Mage::helper("umicrosite")->getCurrentVendor();
	}
	
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
     * @return mixed
     */
    public function getProductFlagLabel()
    {
        return Mage::helper("zolagocatalog/product")->getProductBestFlag($this->getProduct());
    }
	
	/**
	 * @param Mage_Catalog_Model_Category $category
	 * @return string
	 */
	public function getParentCategoryName(Mage_Catalog_Model_Category $category=null) {
		if(is_null($category)){
			$category = $this->getParentCategory();
		}
		return $category->getName();
	}
	
	/**
	 * @param Mage_Catalog_Model_Category $category
	 * @return string
	 */
	public function getParentCategoryUrl(Mage_Catalog_Model_Category $category = null) {
		if(is_null($category)){
			$category = $this->getParentCategory();
		}
        return $category->getUrl();
	}
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getParentCategory() {
		if(!$this->hasData("parent_category")){
			if(Mage::registry('current_category') instanceof Mage_Catalog_Model_Category){
                //load again model because this model don't have all required data
                $model = Mage::getModel("catalog/category")->load(Mage::registry('current_category')->getId());
			}else{
				$model = $this->getParentCategoryAnonymous();//Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId());
			}
			$this->setData("parent_category", $model);
		}
		
		return $this->getData("parent_category");
	}

	public function getParentCategoryAnonymous() {
		$path  = Mage::helper('catalog')->getBreadcrumbPath();

		// Product page and has no path - prepare defualt path
		if(is_array($path) && count($path)==1 &&
			Mage::registry('current_product') instanceof Mage_Catalog_Model_Product){

			$product = Mage::registry('current_product');
			/* @var $product Mage_Catalog_Model_Product */
			$catIds = $product->getCategoryIds();
			$rootId = Mage::app()->getStore()->getRootCategoryId();

			$collection = Mage::getResourceModel('catalog/category_collection');
			/* @var $collection Mage_Catalog_Model_Resource_Category_Collection */

			$collection->addAttributeToFilter("entity_id", array("in"=>$catIds));
			$collection->addAttributeToFilter("is_active", 1);
			$collection->addPathFilter("/$rootId/");

			// Get first category
			if($collection->count()){
				return Mage::getModel("catalog/category")->load($collection->getFirstItem()->getId());
			} else {
				return false;
			}
		}
	}

    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array

     * @return array
     */
    public function getAdditionalDataDetailed($shortForm = false,$showEmpty = true)
    {
        $data = array();
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        //
        $counter = 0;
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                if (is_null($product->getData($attribute->getAttributeCode())) &&  (!$showEmpty)) {
                    continue;
                }
                $value = $attribute->getFrontend()->getValue($product);
                if (!$product->hasData($attribute->getAttributeCode())) {
                    if (!$showEmpty) 
                        continue;
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    if (!$showEmpty) 
                        continue;                        
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }
                if ($shortForm) {
                    if (is_string($value) && strlen($value)) {
                        $data[$attribute->getAttributeCode()] = array(
                            'label' => $attribute->getStoreLabel(),
                            'value' => ($attribute->getFrontendInput() == "multiselect") ? explode(",", $value) : $value,
                            'attribute_order' => $attribute->getColumnAttributeOrder(),
                            'default_order' => $counter,
                        );
                    }
                } else {
                    if (is_string($value) && strlen($value)) {
                        $data[$attribute->getAttributeCode()] = array(
                            'label' => $attribute->getStoreLabel(),
                            'value' => ($attribute->getFrontendInput() == "multiselect") ? explode(",", $value) : $value,
                            'code' => $attribute->getAttributeCode(),
                            'frontend_type' => $attribute->getFrontendInput(),
                            'attribute_order' => $attribute->getColumnAttributeOrder(),
                            'default_order' => $counter,
                            
                        );
                    }
                }
                $counter++;

            }
        }
        //sort by ColumnAttributeOrder
        usort($data, function ($a, $b) {
            if ($a['attribute_order'] != $b['attribute_order']) {        
                return $a['attribute_order'] - $b['attribute_order'];
            } else {
                return $a['default_order'] - $b['default_order'];            
            }
        });
        return $data;
    }
}
