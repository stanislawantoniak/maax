<?php

/**
 * Class Zolago_Solrsearch_Model_Solr_Category_Solr
 */
class Zolago_Solrsearch_Model_Solr_Category_Solr extends Zolago_Solrsearch_Model_Solr
{
    public function prepareQueryData()
    {
        $this->prepareFilterQuery();
        return $this;
    }

    /**
     * @return Unirgy_Dropship_Model_Vendor
     */
    public function getVendor()
    {
        $vendorContext = Mage::helper('umicrosite')->getCurrentVendor();
        return $vendorContext;
    }

    /**
     * Prepare solr filter query paprams
     */
    protected function prepareFilterQuery()
    {
        $filterQuery = Mage::getSingleton('core/session')->getSolrFilterQuery();

        if (!is_array($filterQuery) || !isset($filterQuery)) {
            $filterQuery = array();
        }

        $defaultFilterQuery = array(
            'store_id' => array(Mage::app()->getStore()->getId()),
            'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
            'product_status' => array(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
        );
        $checkInstock = (int)Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInstock > 0) {
            $defaultFilterQuery['instock_int'] = array(Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);
        }
        if($vendorContext = $this->getVendor()){
            $defaultFilterQuery['udropship_vendor_id_int'] = array($vendorContext->getId());
        }

        $filterQuery = array_merge($filterQuery, $defaultFilterQuery);

        $filterQueryArray = array();

        foreach($filterQuery as $key=>$filterItem){


            if(count($filterItem) > 0){
                $query = '';
                foreach($filterItem as $value){
                    $query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';

                }

                $query = trim($query, '+OR+');

                $filterQueryArray[] = $query;
            }
        }

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