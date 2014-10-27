<?php
class Zolago_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    protected function _toHtml() {
		return $this->getLayout()->getBlock('breadcrumbs')->toHtml();
	}
    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Breadcrumbs
     */
    protected function _prepareLayout()
    {
		if(Mage::registry("bc_prepared")){
			return $this;
		}
		
		if (!($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))){
			$this->getLayout()->createBlock('page/html_breadcrumbs', 'breadcrumbs');
		}
       
		// Add vendor
		$vendor = Mage::helper('umicrosite')->getCurrentVendor();        

		if($vendor && $vendor->getId()){
			$breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::helper("zolagodropshipmicrosite")->getBaseUrl()
			));
			$breadcrumbsBlock->addCrumb('vendor', array(
				'label'=>Mage::helper('catalog')->__($vendor->getVendorName()),
				'title'=>Mage::helper('catalog')->__('Vendor'),
				'link'=>Mage::getBaseUrl()
			));
		}else{
			$breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::getBaseUrl()
			));
		}

		$title = array();
		$path  = Mage::helper('catalog')->getBreadcrumbPath();
		
		// Product page and has no path - prepare defualt path
		if(is_array($path) && count($path)==1 && 
			Mage::registry('current_product') instanceof Mage_Catalog_Model_Product){
			
			$product = Mage::registry('current_product');
			/* @var $product Mage_Catalog_Model_Product */
			$catIds = $product->getCategoryIds();
			// Get first category
			if(count($catIds) && isset($catIds[0])){
				$category = Mage::getModel("catalog/category")->load($catIds[0]);
				/* @var $category Mage_Catalog_Model_Category */
				if($category->getId() && ($parents = $category->getParentCategories())){
					$pathIds = array_reverse($category->getPathIds());
					// Remove root category
					array_pop($pathIds);
					foreach($pathIds as $parentId){
						if(isset($parents[$parentId]) && $parents[$parentId] 
							instanceof Mage_Catalog_Model_Category){
							
							$parentCategory = $parents[$parentId];
							array_unshift($path, array(
								"name" => "category" . $parentCategory->getId(),
								"label" => $parentCategory->getName(),
								"link" => $parentCategory->getUrl()
							));
						}
					}
				}
			}
		}

		foreach ($path as $name => $breadcrumb) {
			$breadcrumbsBlock->addCrumb($name, $breadcrumb);
			$title[] = $breadcrumb['label'];
		}

		if ($headBlock = $this->getLayout()->getBlock('head')) {
			$headBlock->setTitle(join($this->getTitleSeparator(), array_reverse($title)));
		}
		
		// Do not prapare bc again
		Mage::register("bc_prepared", true);
		
        return $this;
    }
}
