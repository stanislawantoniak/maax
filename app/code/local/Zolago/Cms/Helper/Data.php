<?php
class Zolago_Cms_Helper_Data extends Mage_Core_Helper_Abstract {
	
	/**
	 * @param Varien_Object $request keys:
	 *	  website - website object or website id. optional current will be used if empty
	 *    component - slider | boxes | inspirations
	 *    vendor - vednro object or vendor id
	 *    category - category object or category id
	 *    
	 * @return string
	 */
	public function requestCmsBlockCode(Varien_Object $request) {
		
		// <composnsnt> default "slider", required
		$parts = array(
			$request->getComponent() ? $request->getComponent() : "slider"
		);
		
		// <code> default current code, required
		if(!($website=$request->getWebsite())){
			$website = Mage::app()->getWebsite();
		}
		if($website instanceof Mage_Core_Model_Website){
			$website = $website->getCode();
		}
		$parts[] = $website;
		
		// v<id> optional
		if($vendor=$request->getVendor()){
			if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
				$vendor = $vendor->getId();
			}
			$parts['v'] = $vendor;
		}
		
		// c<id> optional
		if($category=$request->getCategory()){
			if($category instanceof Mage_Catalog_Model_Category){
				$category = $category->getId();
			}
			$parts['c'] = $category;
		}
		
		// Candidates array
		$tryCodes = array($this->_buildCode($parts));
		
		
		// fallback if category used try parent category. if nout found use without category
		if(isset($parts['c'])){
			$this->_extendForCategory($request->getCategory(), $parts, $tryCodes);
			// No ancestors found - remove category from code
			unset($parts['c']);
			// Try main page vendor
			$tryCodes[] = $this->_buildCode($parts);
		}
		
		$storeId = Mage::app()->getStore()->getId();
		$collection = Mage::getResourceModel('cms/block_collection');
		/* @var $collection Mage_Cms_Model_Resource_Block_Collection */
		$collection->addStoreFilter($storeId);
		$collection->addFieldToFilter("identifier", array("in"=>$tryCodes));
		
		$bestIndex = -1;
		
		// Find best 
		foreach($collection as $cmsBlock){
			/* @var $cmsBlock Mage_Cms_Model_Block */
			if(($idx = array_search($cmsBlock->getIdentifier(), $tryCodes))!==false){
				if($bestIndex<0){
					$bestIndex = $idx;
				}else{
					$bestIndex = min($bestIndex, $idx);
				}
			}
			
		}
		
		if($bestIndex>-1){
			return $tryCodes[$bestIndex];
		}
		
		return null;
	}
	
	/**
	 * @param int|Mage_Catalog_Model_Category $category
	 * @param array $parts
	 * @param array $tryCodes
	 */
	protected function _extendForCategory($category, array $parts, array &$tryCodes) {
		if(!($category instanceof Mage_Catalog_Model_Category)){
			$category = Mage::getModel("catalog/category")->load($category);
		}
		if($category->getId()){
			// Remove root id
			$parentsIds = $category->getParentIds();
			array_shift($parentsIds);
			$parentsIds = array_reverse($parentsIds);
			foreach($parentsIds as $parentId){
				$parts['c'] = $parentId;
				$tryCodes[] = $this->_buildCode($parts);
			}
		}
	}

	/**
	 * @param array $parts
	 * @return string
	 */
	protected function _buildCode(array $parts) {
		$str = "";
		foreach($parts as $key=>$part){
			if(is_string($key)){
				$str .= $key.$part;
			}else{
				$str .= $part;
			}
			$str .= "-";
		}
		return trim($str, "-");
	}
}