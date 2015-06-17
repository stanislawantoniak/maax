<?php
class Zolago_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_View
{

    /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->createBlock('catalog/breadcrumbs');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $product = $this->getProduct();
            $seo = array();
            $title = $product->getMetaTitle();

            if ($title) {
                $headBlock->setTitle($title);
                $seo["dynamic_meta_title"] = $title;
            }
            $keyword = $product->getMetaKeyword();
            $currentCategory = Mage::registry('current_category');
            if ($keyword) {
                $headBlock->setKeywords($keyword);
                $seo["dynamic_meta_keywords"] = $keyword;
            }

            $description = $product->getMetaDescription();
            if ($description) {
                $headBlock->setDescription( ($description) );
                $seo["dynamic_meta_description"] = $description;
            }

            if ($this->helper('catalog/product')->canUseCanonicalTag()) {
                $params = array('_ignore_category' => true, '_no_vendor'=> true);
                $headBlock->addLinkRel('canonical', $product->getUrlModel()->getUrl($product, $params));
            }

            if(isset($seo["dynamic_meta_title"]) && isset($seo["dynamic_meta_keywords"]) && isset($seo["dynamic_meta_description"])){
                return;
            }
            //Dynamic seo fields
            $seoTexts = $this->getProductDynamicSeo($product, $seo);

            $dynamic_meta_title = isset($seoTexts["dynamic_meta_title"]) ? $seoTexts["dynamic_meta_title"] : "";
            $dynamic_meta_keywords = isset($seoTexts["dynamic_meta_keywords"]) ? $seoTexts["dynamic_meta_keywords"] : "";
            $dynamic_meta_description = isset($seoTexts["dynamic_meta_description"]) ? $seoTexts["dynamic_meta_description"] : "";

            $dynamic_meta_title = $this->getAttributesSubstitutions($product, $dynamic_meta_title);
            if(!empty($dynamic_meta_title)){
                $headBlock->setTitle($dynamic_meta_title);
            }
            $dynamic_meta_keywords = $this->getAttributesSubstitutions($product, $dynamic_meta_keywords);
            if (!empty($dynamic_meta_keywords)) {
                $headBlock->setKeywords($dynamic_meta_keywords);
            } elseif ($currentCategory) {
                $headBlock->setKeywords($product->getName());
            }
            $dynamic_meta_description = $this->getAttributesSubstitutions($product, $dynamic_meta_description);
            if(!empty($dynamic_meta_description)){
                $headBlock->setDescription($dynamic_meta_description);
            } else {
                $headBlock->setDescription(Mage::helper('core/string')->substr($product->getDescription(), 0, 255));
            }
            //Dynamic seo fields
        }
    }

    public function getProductDynamicSeo($product, $seo){

        if (!$product instanceof Zolago_Catalog_Model_Product) {
            return $seo;
        }
        $rootId = Mage::helper("zolagosolrsearch")->getRootCategoryId();
        $catIds = $product->getCategoryIds();


        $store = Mage::app()->getStore()->getId();
        $collection = Mage::getModel('catalog/category')->setStoreId($store)
            ->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection->addAttributeToSelect("basic_category");
        //$collection->addAttributeToSelect("name");
        $collection->addAttributeToSelect("dynamic_meta_title", true);
        $collection->addAttributeToSelect("dynamic_meta_keywords", true);
        $collection->addAttributeToSelect("dynamic_meta_description", true);
        $collection->setStoreId($store);
        $collection->addAttributeToFilter("entity_id", array("in" => $catIds));
        $collection->addAttributeToFilter("is_active", 1);
//        $collection->addAttributeToFilter("basic_category", 1);
        $collection->addPathFilter("/$rootId/");
        $collection->setOrder("basic_category", "DESC");
        $collection->setOrder("level", "DESC");
        $collection->setOrder("position", "ASC");
        //$collection->addAttributeToSelect("*");
        //Mage::log($collection->getSelect()->__toString(), null, "dcat1.log");
        $cat = $collection->getFirstItem();
        //Mage::log($cat->getData(), null, "dcat1.log");


        if($cat->getData("basic_category") == 1){
            $dynamic_meta_title = $cat->getData("dynamic_meta_title");
            $dynamic_meta_keywords = $cat->getData("dynamic_meta_keywords");
            $dynamic_meta_description = $cat->getData("dynamic_meta_description");
            if (!empty($dynamic_meta_title) && !isset($seo["dynamic_meta_title"])) {
                $seo["dynamic_meta_title"] = $dynamic_meta_title;
            }
            if (!empty($dynamic_meta_keywords) && !isset($seo["dynamic_meta_keywords"])) {
                $seo["dynamic_meta_keywords"] = $dynamic_meta_keywords;
            }
            if (!empty($dynamic_meta_description) && !isset($seo["dynamic_meta_description"])) {
                $seo["dynamic_meta_description"] = $dynamic_meta_description;
            }

            if (isset($seo["dynamic_meta_title"])
                && isset($seo["dynamic_meta_keywords"])
                && isset($seo["dynamic_meta_description"])
            ) {
                return $seo;
            }
            //Go up by category tree
            $seo = $this->getDynamicMetaTagsInParents($seo, $cat);
        } else {
            $seo = $this->getDynamicMetaTagsInParents($seo, $cat);
        }
        return $seo;
    }

    public function getDynamicMetaTagsInParents($seo, $category)
    {
        $rootId = Mage::helper("zolagosolrsearch")->getRootCategoryId();
        $categoryParentId = $category->getData("parent_id");

        if ((int)$categoryParentId == 0 ||
            $categoryParentId == $rootId ||
            (isset($seo["dynamic_meta_title"])
                && isset($seo["dynamic_meta_keywords"])
                && isset($seo["dynamic_meta_description"]))
        ) {

            return $seo;
        } else {
            $categoryParent = Mage::getModel('catalog/category')->load($categoryParentId);


            $dynamic_meta_title = $categoryParent->getData("dynamic_meta_title");
            if (!isset($seo["dynamic_meta_title"]) && !empty($dynamic_meta_title)) {
                $seo["dynamic_meta_title"] = $dynamic_meta_title;
            }

            $dynamic_meta_keywords = $categoryParent->getData("dynamic_meta_keywords");
            if (!isset($seo["dynamic_meta_keywords"]) && !empty($dynamic_meta_keywords)) {
                $seo["dynamic_meta_keywords"] = $dynamic_meta_keywords;
            }

            $dynamic_meta_description = $categoryParent->getData("dynamic_meta_description");
            if (!isset($seo["dynamic_meta_description"]) && !empty($dynamic_meta_description)) {
                $seo["dynamic_meta_description"] = $dynamic_meta_description;
            }


            return $this->getDynamicMetaTagsInParents($seo, $categoryParent);
        }
    }
    /**
     * @param $product Zolago_Catalog_Model_Product
     * @param $seoText
     * @return string
     */
    public function getAttributesSubstitutions($product, $seoText){
        Mage::log($seoText, null, "dynamic.log");
        $result = "";
        if(!$product instanceof Zolago_Catalog_Model_Product){
            return $result;
        }
        if(empty($seoText)){
            return $result;
        }
        preg_match_all('#\$([a-zA-Z0-9_]+)#', $seoText, $matches, PREG_SET_ORDER);
        preg_match_all('(\$current_date\sformat=\"([\w\-]+)\")', $seoText, $matchesOfDate, PREG_SET_ORDER);

        $attributesFoundInLine = array();

        if(!empty($matches)){
            foreach($matches as $match){
                $attributesFoundInLine[$match[1]] = $match[1];
            }
            unset($match);
        }
        $dates = array();
        if(!empty($matchesOfDate)){
            foreach($matchesOfDate as $match){
                $dates[$match[1]] = $match[1];
            }
            unset($match);
        }
        if(empty($attributesFoundInLine)){
            return $seoText;
        }



        $labels = array();

        foreach ($attributesFoundInLine as $attributeCode) {

            $label = "";

            $attribute = $product->getResource()
                ->getAttribute($attributeCode);
            if (!$attribute) {
                $labels[$attributeCode] = $label;
                continue;
            }
            $frontend_input = $attribute->getData("frontend_input");
            //Mage::log($attributeCode. "--".$frontend_input, null, "codes.log");
            switch($frontend_input){
                case "select":
                    $label = $product
                        ->setData($attributeCode, $product->getData($attributeCode))
                        ->getAttributeText($attributeCode);
                    break;
                case "price":
                    $label = Mage::helper('core')->currency($product->getResource()
                        ->getAttribute($attributeCode)
                        ->getFrontend()
                        ->getValue($product), true, false);
                    break;
                case "multiselect":
                    $value = $product->getResource()
                        ->getAttribute($attributeCode)
                        ->getFrontend()
                        ->getValue($product);
                    $label = !empty($value) ? explode(",", $value)[0] : "";
                    break;
                default:
                    $label = $product->getResource()
                        ->getAttribute($attributeCode)
                        ->getFrontend()
                        ->getValue($product);
            }

            $labels[$attributeCode] = $label;
            unset($label);
            unset($attributeCode);
            unset($attribute);
            unset($frontend_input);
        }

        $subst = array();
        foreach($labels as $code => $strItem){
            $subst['{$'.$code.'}'] = $strItem;
            $subst['{$'.$code.' first_letter=capital}'] = ucfirst($strItem);
        }
        if(!empty($dates)){
            foreach($dates as $format){
                $subst['{$current_date format="'.$format.'"}'] = date($format);
            }
        }



        return strtr($seoText, $subst);
    }

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
	    /** @var Zolago_catalog_Helper_Product $helper */
	    $helper = Mage::helper("zolagocatalog/product");
        return $helper->getProductBestFlag($this->getProduct());
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
			if(($currCat = Mage::registry('current_category')) instanceof Mage_Catalog_Model_Category){
                $name = $currCat->getName();
                if (empty($name)) {
                    // load again model because this model don't have all required data
                    $model = Mage::getModel("catalog/category")->load(Mage::registry('current_category')->getId());
                    Mage::unregister('current_category');
                    Mage::registry('current_category', $model);
                } else {
                    $model = Mage::registry('current_category');
                }
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
                            'code' => $attribute->getAttributeCode(),
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
