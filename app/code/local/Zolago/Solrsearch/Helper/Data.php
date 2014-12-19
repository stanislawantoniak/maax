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
    protected $numFound;

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
                                    "is_bestseller_int"		=> "is_bestseller",
                                    "product_flag_int"		=> "product_flag",
                                    "special_price_decimal"	=> "special_price",
                                    "special_from_date_varchar"			=> "special_from_date",
                                    "special_to_date_varchar"			=> "special_to_date",
                                    "udropship_vendor_id_int"			=> "udropship_vendor",
                                    "udropship_vendor_logo_varchar"		=> "udropship_vendor_logo",
                                    "udropship_vendor_url_key_varchar"	=> "udropship_vendor_url_key",
                                    "udropship_vendor_varchar"			=> "udropship_vendor_name",
                                    "manufacturer_logo_varchar"			=> "manufacturer_logo",
                                    "manufacturer_varchar"				=> "manufacturer"
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
     * @param Zolago_Solrsearch_Model_Catalog_Product_List $listModel
     * @return array
     */
    public function prepareAjaxProducts(Zolago_Solrsearch_Model_Catalog_Product_List $listModel) {
        // Create product list
        $products = array();

        foreach ($listModel->getCollection() as $product) {
            /* @var $product Zolago_Solrsearch_Model_Catalog_Product */
            $_product = $product->getData();
            $_product['listing_resized_image_url'] = (string)$product->getListingResizedImageUrl();
            $_product['listing_resized_image_info'] = $product->getListingResizedImageInfo();
            $_product['udropship_vendor_logo_url'] = (string)$product->getUdropshipVendorLogoUrl();
            $_product['manufacturer_logo_url'] = (string)$product->getManufacturerLogoUrl();
            $_product['is_discounted'] = (int)$product->isDiscounted();
            $_product['price'] = (float)$product->getPrice();
            $_product['final_price'] = (float)$product->getFinalPrice();
            $_product['currency'] = (string)$product->getCurrency();
            $products[] = $_product;
        }

        return $products;
    }

    /**
     * @param array $params
     * @return array
     */
    public function processFinalParams(array $params = array(), $force = false) {

        // Unset positition if regular http request

        if(!Mage::helper("zolagocommon")->isGoogleBot() || $force) {

            $params['rows'] = null;
            $params['start'] = null;
            $params['page'] = null;

        }

        $params["_"] = null;

        return $params;
    }

    /**
     *
     * @param type $storeId
     * @return type
     */
    public function getCoresByStoreId($storeId) {
        $cores = array();
        foreach($this->getCores() as $core=>$data) {
            if(isset($data['stores'])) {
                $ids = explode(",", trim($data['stores'], ","));
                if(in_array($storeId,$ids)) {
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
        if(!$this->_cores) {
            $this->_cores  = (array) Mage::getStoreConfig('solrbridgeindices', 0);
        }
        return $this->_cores;
    }

    /**
     * @return array
     */
    public function getAvailableCores() {
        $cores = array();
        foreach($this->getCores() as $core => $data) {
            if(isset($data['stores'])) {
                $ids = array_filter(explode(",", trim($data['stores'], ",")));
                if(count($ids)) {
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
        if(!is_array($this->_availableStoreIds)) {
            $this->_availableStoreIds = array();
            foreach($this->getCores() as $core=>$data) {
                if(isset($data['stores'])) {
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
                   ->addAttributeToFilter('include_in_menu', '1');

        $html = '';

        if($allCats->count() > 0) {

            foreach ($allCats as $category) {

                $html .= '<option value="' . $category->getId() . '" >' . str_repeat("&nbsp;", 4 * $level)
                         . $category->getName() . "</option>";
            }
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
     * Construct context search selector Array
     * @return array
     */
    public function getContextSelectorArray()
    {
        /** @var $this Zolago_Solrsearch_Helper_Data */
        /** @var Zolago_Dropship_Model_Vendor $_vendor */

        $array = array();

        $_vendor = Mage::helper('umicrosite')->getCurrentVendor();
        $currentCategory = $this->getCurrentCategory();
        $queryText = Mage::helper('solrsearch')->getParam('q');

        $array['url'] = Mage::getUrl("search/index/index", array("_secure"=>Mage::app()->getStore()->isCurrentlySecure()));
        $array['method'] = "get";
        $array['input_name'] = 'q';
        $array['select_name'] = 'scat';

        $array['input_current_value'] = $queryText;

        $array['select_options'] = array();

        $array['select_options'][0] = array(
                                          'value' => 0,
                                          'text' => $this->__('Everywhere'),
                                          'selected' => true
                                      );

        $array['input_empty_text'] = $this->__('Search entire store here...');

        // This vendor
        if ($_vendor && $_vendor->getId()) {

            /** @var Zolago_DropshipMicrosite_Helper_Data $helperZDM */
            $helperZDM = Mage::helper("zolagodropshipmicrosite");
            $vendorRootCategoryId = $helperZDM->getVendorRootCategoryObject()->getId();

            $array['select_options'][] = array(
                                             'value' => "{$vendorRootCategoryId}",
                                             //like 'everywhere' is root category ( zero ),
                                             //so when vendor is set the category is his root category
                                             'text' => $_vendor->isBrandshop() ? $this->__('This brandshop') : $this->__('This vendor'),
                                             'selected' => true,
                                         );

            $array['input_empty_text'] = $this->__('Search in ') . $_vendor->getVendorName() . '...';

            // Make "Everywhere" unselected
            $array['select_options'][0]['selected'] = false;
        }
        else {

            // Categories are only shown for global context and not for vendor context
            $allCats = Mage::getModel('catalog/category')->getCollection()
                       ->addAttributeToSelect('*')
                       ->addAttributeToFilter('is_active', '1')
                       ->addAttributeToFilter( self::ZOLAGO_USE_IN_SEARCH_CONTEXT , array('eq' => 1))
                       ->addAttributeToFilter('include_in_menu', '1');

            foreach ($allCats as $category) {

                if($currentCategory && $currentCategory->getId() == $category->getId()) {

                }
                else
                {
                    $selected = false;

                    $array['select_options'][] = array(
                                                     'text' => $category->getName(),
                                                     'value' => $category->getId(),
                                                     'selected' => $selected
                                                 );
                }
            }

            if ($currentCategory) {


                $rootCategory = Mage::app()->getStore()->getRootCategoryId();

                if($rootCategory != $currentCategory->getId()) {

                    $selected = true;

                    $array['select_options'][] = array(
                                                     'text' => $this->__('This category'),
                                                     'value' => $currentCategory->getId(),
                                                     'selected' => $selected
                                                 );

                    $array['input_empty_text'] = $this->__('Search in ') . $currentCategory->getName() . "...";

                    // Make "Everywhere" unselected
                    $array['select_options'][0]['selected'] = false;
                }
            }
        }
        return $array;
    }

    /**
     * Retrive info from solar for sibling categories
     *
     * @return array
     */
    public function getAllCatgoryData($parent_category, $rollback_category = NULL) {

        if($all_data = Mage::registry('all_category_data')) {
            return $all_data;
        }

        $facetfield = 'category_facet';
        $all_data = array();

        // Get query
        $queryText = Mage::helper('solrsearch')->getParam('q');
        if(empty($queryText)) {
            $queryText = '*';
        }

        $solrModel = Mage::getModel('solrsearch/solr');

        // Set parent category
        $solrModel->setCurrentCategory($parent_category);

        $resultSet = $solrModel->query($queryText);

        // Rollback
        if($rollback_category) {
            $solrModel->setCurrentCategory($rollback_category);
        }

        if(isset($resultSet['facet_counts']['facet_fields'][$facetfield]) && is_array($resultSet['facet_counts']['facet_fields'][$facetfield]))
        {
            $all_data = $resultSet['facet_counts']['facet_fields'][$facetfield];
        }

        if($all_data) {
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

        foreach($this->_solrToMageMap as $solr=>$mage) {
            if(isset($item[$solr])) {
                $product->setDataUsingMethod($mage, $item[$solr]);
            }
        }

        $product->setId((int)$product->getId());

        return $product;
    }

    /**
     * @return array
     */
    public function getSolrDocFileds() {
        return array_keys($this->_solrToMageMap);
    }

    /**
     * @param Mage_Catalog_Model_Product $model
     * @return string | empty_string
     */
    public function getListingResizedImageUrl(Mage_Catalog_Model_Product $model) {

        if(!$model->hasData("listing_resized_image_url")) {

            $return = null;
            try {
                $return = Mage::helper('catalog/image')->
                          init($model, 'image')->
                          keepAspectRatio(true)->
                          constrainOnly(true)->
                          keepFrame(false)->
                          resize(300,null);
            } catch (Exception $ex) {
                Mage::logException($ex);
            }

            $model->setData("listing_resized_image_url", $return . ""); // Cast to string
        }

        return $model->getData("listing_resized_image_url");
    }


    /**
     * @return int
     */
    public function getNumFound() {
        if(is_null($this->numFound)) {
            $num = Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getCollection()->getSolrData("response", "numFound");
            if (is_numeric($num)) {
                $this->numFound = $num;
            } else {
                $this->numFound = 0;
            }
        }
        return $this->numFound;
    }

    public function getSolrRealQ() {
        /** @var Zolago_Solrsearch_Model_Catalog_Product_List $clp */
        $cpl = Mage::getSingleton('zolagosolrsearch/catalog_product_list');
        return $cpl->getCollection()->getSolrData("responseHeader","params", "q");
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
        if(Mage::registry('current_category')) {
            if(Mage::registry('vendor_current_category')) {
                return Mage::registry('vendor_current_category');
            } else {
                return Mage::registry('current_category');
            }
        }
        return  Mage::registry('vendor_current_category');
    }

    /**
     * @return string
     */
    public function getQueryText() {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list')->getQueryText();
    }

    /**
     * @return string URL
     */
    public function getRemoveQueryTextUrl() {
        return Mage::getUrl('todo');//@todo
    }

    /**
     * @return bool
     */
    public function canShowCurrentCategorySearch() {
        return true; //@todo
    }

    /**
     * @return string
     */
    public function getCurrentCategoryString() {
        return "[dev]Bielizna"; //@todo
    }


}