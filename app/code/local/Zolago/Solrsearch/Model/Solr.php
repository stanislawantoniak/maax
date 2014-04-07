<?php
class Zolago_Solrsearch_Model_Solr extends SolrBridge_Solrsearch_Model_Solr
{
	protected $_specialKeys = array(
		'is_new_facet',
		'is_bestseller_facet',
		'product_flag_facet'
	);
	
    public function prepareCleanFlagQueryData()
    {
        $this->prepareFieldList();
        if (!$this->isAutocomplete) {
            $this->preparePagingAndSorting();
        }
        $this->prepareFacetAndBoostFields();
		$this->prepareCleanFlagFilterQuery();
        $this->prepareSynonym();
        return $this;
    }	
	
    /**
     * Prepare solr filter query paprams
     */
    protected function prepareFilterQuery()
    {
        $filterQuery = Mage::getSingleton('core/session')->getSolrFilterQuery();
        $standardFilterQuery = array();
        if ($standardFilterQuery = $this->getStandardFilterQuery()) {
            $filterQuery = $this->getStandardFilterQuery();
        }

        if (!is_array($filterQuery) || !isset($filterQuery)) {
            $filterQuery = array();
        }

        $defaultFilterQuery = array(
                'store_id' => array(Mage::app()->getStore()->getId()),
                'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
                'product_status' => array(1)
        );
        $checkInstock =  (int) Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInstock > 0) {
        	$defaultFilterQuery['instock_int'] = array(1);
        }

        $filterQuery = array_merge($filterQuery, $defaultFilterQuery);

        /**
         * Ignore the following section if the request is for autocomplete
         * The purpose is the speed up autocomplete
         */
		
        if (!$this->isAutocomplete) {

            if (in_array(Mage::app()->getRequest()->getRouteName(), array('catalog', 'umicrosite'))) {

                $layer = Mage::getSingleton('catalog/layer');
				if( Mage::registry("vendor_current_category") instanceof Mage_Catalog_Model_Category){
					$_category = Mage::registry("vendor_current_category");
				}else{
					$_category = $layer->getCurrentCategory();
				} 
                $currentCategoryId = $_category->getId();
				
				
                if (empty($filterQuery['category_id'])) {
                    $filterQuery['category_id'] = array($currentCategoryId);
                }
				

                $filterQuery['filter_visibility_int'] = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

                //Check category is anchor
                if ($_category->getIsAnchor()) {
                    $childrenIds = $_category->getAllChildren(true);

                    if (is_array($childrenIds) && isset($filterQuery['category_id']) && is_array($filterQuery['category_id'])) {
                        if (!isset($standardFilterQuery['category_id'])){
                            $filterQuery['category_id'] = array_merge($filterQuery['category_id'], $childrenIds);
                        }
                    }
                }
            };
        }

        $filterQueryArray = array();
		$extendedFilterQueryArray = array();
        $rangeFields = $this->rangeFields;

        foreach($filterQuery as $key=>$filterItem){
            //Ignore cateory facet - using category instead
            if ($key == 'category_facet') {
                continue;
            }

            if(count($filterItem) > 0){
                $query = '';
				$extendedQuery = '';
                foreach($filterItem as $value){
                    if ($key == 'price_decimal') {
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                    }else if($key == 'price'){
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
					}
					else if(in_array($key, $this->_specialKeys, true)) {
						$extendedQuery .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
					}
					else{
                        $face_key = substr($key, 0, strrpos($key, '_'));
                        if ($key == 'price_facet') {
                            $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                        }
                        else if(array_key_exists($face_key, $rangeFields))
                        {
                            $query .= $rangeFields[$face_key].':['.urlencode(trim(addslashes($value))).']+OR+';
                        }else{
                            $query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
                        }
                    }
                }

				if ($query) {
					$query = trim($query, '+OR+');
					$filterQueryArray[] = $query;
				}
				
				if ($extendedQuery) {
					$extendedQuery				= trim($extendedQuery, '+OR+');
					$extendedFilterQueryArray[] = $extendedQuery;					
				}				
            }
        }

		//Add Vendor Facet to Filter Microsite Products - Start
		$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		if ($_vendor && $_vendor->getId()) {
			$vendorQuery = 'udropship_vendor_facet'.':%22'.urlencode(trim(addslashes($_vendor->getVendorName()))).'%22+OR+';
			$vendorQuery = trim($vendorQuery, '+OR+');
			$filterQueryArray[] = $vendorQuery;
		}
		//Add Vendor Facet to Filter Microsite Products - End

        $filterQueryString = '';

        if(count($filterQueryArray) > 0) {
            if(count($filterQueryArray) < 2) {
                $filterQueryString .= $filterQueryArray[0];
            }else{
                $filterQueryString .= '%28'.@implode('%29+AND+%28', $filterQueryArray);
            }
        }

        if(count($extendedFilterQueryArray) > 0) {
			$filterQueryString .= '%29+AND+%28'.@implode('+OR+', $extendedFilterQueryArray).'%29';
        } else {
			$filterQueryString .= '%29';
		}

        $this->filterQuery = $filterQueryString;
    }
	
    /**
     * Prepare solr filter query paprams
     */
    protected function prepareCleanFlagFilterQuery()
    {
        $filterQuery = Mage::getSingleton('core/session')->getSolrFilterQuery();
        $standardFilterQuery = array();
        if ($standardFilterQuery = $this->getStandardFilterQuery()) {
            $filterQuery = $this->getStandardFilterQuery();
        }

        if (!is_array($filterQuery) || !isset($filterQuery)) {
            $filterQuery = array();
        }

        $defaultFilterQuery = array(
                'store_id' => array(Mage::app()->getStore()->getId()),
                'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
                'product_status' => array(1)
        );
        $checkInstock =  (int) Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInstock > 0) {
        	$defaultFilterQuery['instock_int'] = array(1);
        }

        $filterQuery = array_merge($filterQuery, $defaultFilterQuery);

        /**
         * Ignore the following section if the request is for autocomplete
         * The purpose is the speed up autocomplete
         */
		
        if (!$this->isAutocomplete) {

            if (in_array(Mage::app()->getRequest()->getRouteName(), array('catalog', 'umicrosite'))) {

                $layer = Mage::getSingleton('catalog/layer');
				if( Mage::registry("vendor_current_category") instanceof Mage_Catalog_Model_Category){
					$_category = Mage::registry("vendor_current_category");
				}else{
					$_category = $layer->getCurrentCategory();
				} 
                $currentCategoryId = $_category->getId();
				
				
                if (empty($filterQuery['category_id'])) {
                    $filterQuery['category_id'] = array($currentCategoryId);
                }
				

                $filterQuery['filter_visibility_int'] = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();

                //Check category is anchor
                if ($_category->getIsAnchor()) {
                    $childrenIds = $_category->getAllChildren(true);

                    if (is_array($childrenIds) && isset($filterQuery['category_id']) && is_array($filterQuery['category_id'])) {
                        if (!isset($standardFilterQuery['category_id'])){
                            $filterQuery['category_id'] = array_merge($filterQuery['category_id'], $childrenIds);
                        }
                    }
                }
            };
        }

        $filterQueryArray = array();
        $rangeFields = $this->rangeFields;

        foreach($filterQuery as $key=>$filterItem){
            //Ignore cateory facet - using category instead
            if ($key == 'category_facet') {
                continue;
            }

            if(count($filterItem) > 0){
                $query = '';
				$extendedQuery = '';
                foreach($filterItem as $value){
                    if ($key == 'price_decimal') {
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                    }else if($key == 'price'){
                        $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
					}
					else if(in_array($key, $this->_specialKeys, true)) {
						continue;
					}
					else{
                        $face_key = substr($key, 0, strrpos($key, '_'));
                        if ($key == 'price_facet') {
                            $query .= $this->priceFieldName.':['.urlencode(trim($value).'.99999').']+OR+';
                        }
                        else if(array_key_exists($face_key, $rangeFields))
                        {
                            $query .= $rangeFields[$face_key].':['.urlencode(trim(addslashes($value))).']+OR+';
                        }else{
                            $query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
                        }
                    }
                }

				if ($query) {
					$query = trim($query, '+OR+');
					$filterQueryArray[] = $query;
				}
            }
        }

		//Add Vendor Facet to Filter Microsite Products - Start
		$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		if ($_vendor && $_vendor->getId()) {
			$vendorQuery = 'udropship_vendor_facet'.':%22'.urlencode(trim(addslashes($_vendor->getVendorName()))).'%22+OR+';
			$vendorQuery = trim($vendorQuery, '+OR+');
			$filterQueryArray[] = $vendorQuery;
		}
		//Add Vendor Facet to Filter Microsite Products - End

        $filterQueryString = '';

        if(count($filterQueryArray) > 0) {
            if(count($filterQueryArray) < 2) {
                $filterQueryString .= $filterQueryArray[0];
            }else{
                $filterQueryString .= '%28'.@implode('%29+AND+%28', $filterQueryArray).'%29';
            }
        }

        $this->filterQuery = $filterQueryString;
    }		
}