<?php

/**
 * Class Zolago_Solrsearch_Model_Solr_Category_Solr
 */
class Zolago_Solrsearch_Model_Solr_Category_Solr extends Zolago_Solrsearch_Model_Solr
{
    protected $_category;
    protected $_vendorContext;

    public function getCategory()
    {
        return $this->_category;
    }

    public function setCategory($category)
    {
        return $this->_category = $category;
    }

    public function getVendorContext()
    {
        return $this->_vendorContext;
    }

    public function setVendorContext($vendor)
    {
        return $this->_vendorContext = $vendor;
    }

    public function prepareQueryData()
    {
        $this->prepareFieldList();
        $this->prepareFilterQuery();
        return $this;
    }


    /**
     * Determine which fields will be selected
     */
    protected function prepareFieldList()
    {
        if (empty($this->fieldList)) {
            //$this->fieldList = array('products_id', 'category_id', 'store_id', 'website_id');
            $this->fieldList = array();
        }
    }


    /**
     * Prepare solr filter query paprams
     */
    protected function prepareFilterQuery()
    {
        $filterQuery = array();

        $defaultFilterQuery = array(
            'store_id' => array(Mage::app()->getStore()->getId()),
            'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
            'product_status' => array(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)

        );
        $checkInStock = (int)Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInStock > 0) {
            $defaultFilterQuery['instock_int'] = array(Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);
        }



        $filterQuery = array_merge($filterQuery, $defaultFilterQuery);
        $_category = $this->getCategory();
        $currentCategoryId = $_category->getId();

        if (empty($filterQuery['category_id'])) {
            $filterQuery['category_id'] = array($currentCategoryId);
        }
        $filterQuery['filter_visibility_int'] = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
        //Check category is anchor
        if ($_category->getIsAnchor()) {
            $childrenIds = $_category->getAllChildren(true);

            if (is_array($childrenIds) && isset($filterQuery['category_id']) && is_array($filterQuery['category_id'])) {
                if (!isset($standardFilterQuery['category_id'])) {
                    $filterQuery['category_id'] = array_merge($filterQuery['category_id'], $childrenIds);
                }
            }
        }

        $filterQueryArray = array();

        foreach ($filterQuery as $key => $filterItem) {
            if (count($filterItem) > 0) {
                $query = '';
                foreach ($filterItem as $value) {
                    $query .= $key . ':%22' . urlencode(trim(addslashes($value))) . '%22+OR+';
                }
                $query = trim($query, '+OR+');

                $filterQueryArray[] = $query;
            }
        }

        $vendorContext = $this->getVendorContext();
        if ($vendorContext && $vendorContext->getId()) {

            // Vendor context - add vendor/brandshop filter according to vendor type
            switch ($vendorContext->getVendorType()) {
                case Zolago_Dropship_Model_Source::VENDOR_TYPE_BRANDSHOP:
                    $filterQueryArray[] = 'udropship_brandshop_id_int' . ':' . $vendorContext->getId();
                    break;
                case Zolago_Dropship_Model_Source::VENDOR_TYPE_STANDARD:
                    $filterQueryArray[] = 'udropship_vendor_id_int' . ':' . $vendorContext->getId();
                    break;
            }
        }


        $filterQueryString = '';

        if (count($filterQueryArray) > 0) {
            if (count($filterQueryArray) < 2) {
                $filterQueryString .= $filterQueryArray[0];
            } else {
                $filterQueryString .= '%28' . @implode('%29+AND+%28', $filterQueryArray) . '%29';
            }
        }
        //Mage::log($filterQueryString,null,"TEST_4.log");
        $this->filterQuery = $filterQueryString;
    }
}