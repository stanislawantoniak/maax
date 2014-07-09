<?php
/**
 * Solr helper
 *
 *
 * @category    Zolago
 * @package     Zolago_Solrsearch
 */
class Zolago_Solrsearch_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ZOLAGO_USE_IN_SEARCH_CONTEXT = 'use_in_search_context';
	
	/**
	 * @var array
	 */
	protected $_solrToMageMap = array(
		"products_id"			=> "id",
		"product_type_static"	=> "type_id",
		"name_varchar"			=> "name",
		"store_id"				=> "store_id",
		"website_id"			=> "website_id",
		"category_id"			=> "category_ids",
		"sku_static"			=> "sku",
		"vsku_text"				=> "vsku",
		"in_stock_int"			=> "in_stock",
		"product_status"		=> "status",
		"image_varchar"			=> "image",
		"wishlist_count_int"	=> "wishlist_count",
		"tax_class_id_int"		=> "tax_class_id",
		"is_new_int"			=> "is_new",
		"product_rating_int"	=> "product_rating",
		"is_bestseller_int"		=> "bestseller_int",
		"special_price_decimal"	=> "special_price",
		"special_from_date_varchar"			=> "special_from_date",
		"special_to_date_varchar"			=> "special_to_date",
		"udropship_vendor_id_int"			=> "udropship_vendor",
		"udropship_vendor_logo_varchar"		=> "udropship_vendor_logo",
		"udropship_vendor_url_key_varchar"	=> "udropship_vendor_url_key",
		"udropship_vendor_varchar"			=> "udropship_vendor_name"
	);

	/**
	 * @var array
	 */
	protected $_cores;
	/**
	 * @var array
	 */
	protected $_availableStoreIds;
	
	/**
	 * 
	 * @param type $storeId
	 * @return type
	 */
	public function getCoresByStoreId($storeId) {
		$cores = array();
		foreach($this->getCores() as $core=>$data){
			if(isset($data['stores'])){
				$ids = explode(",", trim($data['stores'], ","));
				if(in_array($storeId,$ids)){
					$cores[] = $core;
				}
			}
		}
		return $cores;
	}
	
	/**
	 * @return array
	 */
	public function getCores() {
		if(!$this->_cores){
			$this->_cores  = (array) Mage::getStoreConfig('solrbridgeindices', 0);
		}
		return $this->_cores;
	}
	
	/**
	 * @return array
	 */
	public function getAvailableCores() {
		$cores = array();
		foreach($this->getCores() as $core => $data){
			if(isset($data['stores'])){
				$ids = array_filter(explode(",", trim($data['stores'], ",")));
				if(count($ids)){
					$cores[$core] = true;
				}
			}
		}
		return array_keys($cores);
	}
	
	/**
	 * Returns vaialble stores (cores with has assigned store)
	 * @return array
	 */
	public function getAvailableStores() {
		if(!is_array($this->_availableStoreIds)){
			$this->_availableStoreIds = array();
			foreach($this->getCores() as $core=>$data){
				if(isset($data['stores'])){
					$ids = explode(",", trim($data['stores'], ","));
					$this->_availableStoreIds = array_merge($this->_availableStoreIds, $ids);
				}
			}
			$this->_availableStoreIds = array_values(
					array_filter(array_unique($this->_availableStoreIds)));
		}
		return $this->_availableStoreIds;
	}
	
    public function getTreeCategoriesSelect($parentId, $level, $cat)
    {
        if ($level > 5) {
            return '';
        } // Make sure not to have an endless recursion
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
             ->addAttributeToFilter( self::ZOLAGO_USE_IN_SEARCH_CONTEXT , array('eq' => 1))
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId));
			
        $html = '';
        foreach ($allCats as $category) {
        	
            $selected = '';
            if($category->getId() == $cat){
                $selected = ' selected="selected" ';
            }
            $html .= '<option value="' . $category->getId() . '" '. $selected.'>' . str_repeat("&nbsp;", 4 * $level)
                . $category->getName() . "</option>";
            // $subcats = $category->getChildren();
            // if ($subcats != '') {
                // $html .= self::getTreeCategoriesSelect($category->getId(), $level + 1,$cat);
            // }
        }
        return $html;
    }

    public function getTreeCategories($parentId, $isChild)
    {

        $cats = array();
        $allCats = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter(self::ZOLAGO_USE_IN_SEARCH_CONTEXT, array('eq' => 1))
            ->addAttributeToFilter('include_in_menu', '1')
            ->addAttributeToFilter('parent_id', array('eq' => $parentId));

        foreach ($allCats as $category) {
            $cats[$category->getId()]['id'] = $category->getId();
            $cats[$category->getId()]['name'] = Mage::helper('catalog')->__($category->getName());
            $subCats = $category->getChildren();
            if (strlen($subCats)>0) {
                $cats[$category->getId()]['sub'] = self::getTreeCategories($category->getId(), true);
            }
        }

        return $cats;

    }

    public function getContextUrl()
    {
        $uri = '/zolagosolrsearch/context';
        return $uri;
    }

    /**
     * Construct context search selector
     * @return string
     */
    public function getContextSelectorHtml()
    {
        $filterQuery = (array)Mage::getSingleton('core/session')->getSolrFilterQuery();

		$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		
        $selectedContext = 0;
        if (isset($filterQuery['category_id']) && isset($filterQuery['category_id'][0])) {
            $selectedContext = $filterQuery['category_id'][0];
        }
		
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
		// When in the vendor context grab root category
		if($_vendor && $_vendor->getId()){
			$vendor_root_category = Mage::registry('vendor_current_category');
			if($vendor_root_category){
				$rootCatId = $vendor_root_category->getId();
			}
		}
		
        $catListHtmlSelect = '<select name="scat">'
            . '<option value="0">' . Mage::helper('catalog')->__('Everywhere') . '</option>';
		
		if ($_vendor && $_vendor->getId()) {
	        $catListHtmlSelect .= '<option selected="selected" value="' . $_vendor->getId() . '">' . $this->__('All ') . $_vendor->getVendorName() . '</option>';
		}
		
        $catListHtmlSelect .= self::getTreeCategoriesSelect($rootCatId, 0, $selectedContext);

        if (Mage::registry('current_category')) {
            $catListHtmlSelect
                .= '<option value="' . Mage::registry('current_category')->getId() . '">'
                . Mage::helper('catalog')->__('This category')
                . '</option>';
        }
	
        $catListHtmlSelect .= "</select>";

        return $catListHtmlSelect;
    }

	/**
	 * Retrive info from solar for sibling categories
	 * 
	 * @return array
	 */
	public function getAllCatgoryData(){
		
		if($all_data = Mage::registry('all_category_data')){
			return $all_data;	
		}
		
		$facetfield = 'category_facet';
		$all_data = array();
		
		// Get query		
		$queryText = Mage::helper('solrsearch')->getParam('q');
		if(empty($queryText)){
	    	$queryText = '*';
		}
		
		// Remove category from filter query
		$params = Mage::app()->getRequest()->getParams();
		
		if(isset($params['fq']['category_id'])){
			unset($params['fq']['category_id']);
			Mage::app()->getRequest()->setParams($params);
		} 
		
		$solrModel = Mage::getModel('solrsearch/solr');
		
		$solrModel->isGlobalSearch();
		
		$resultSet = $solrModel->query($queryText);
		
    	if(isset($resultSet['facet_counts']['facet_fields'][$facetfield]) && is_array($resultSet['facet_counts']['facet_fields'][$facetfield]))
    	{
    		$all_data = $resultSet['facet_counts']['facet_fields'][$facetfield];
    	}
		
		if($all_data){
			Mage::register('all_category_data', $all_data);
		}
		
		return $all_data;
	}
		
	/**
	 * Map solr docuemnt data to local ORM product
	 * @param array $item
	 * @param Mage_Catalog_Model_Product $product
	 * @return Mage_Catalog_Model_Product
	 */
	public function mapSolrDocToProduct(array $item, Mage_Catalog_Model_Product $product) {
		
		foreach($this->_solrToMageMap as $solr=>$mage){
			if(isset($item[$solr])){
				$product->setDataUsingMethod($mage, $item[$solr]);
			}
		}
		
		return $product;
	}
	
	/**
	 * @return array
	 */
	public function getSolrDocFileds() {
		return array_keys($this->_solrToMageMap);
	}
}