<?php
class Zolago_Solrsearch_Model_Solr_Vendor_Landingpage_Product_Solr extends Zolago_Solrsearch_Model_Solr
{
    /**
     * Prepare solr filter query paprams
     */
    protected function prepareFilterQuery()
    {
        $filterQuery = array(
            'store_id' => array(Mage::app()->getStore()->getId()),
            'website_id' => array(Mage::app()->getStore()->getWebsiteId()),
            'product_status' => array(1)
        );

        $checkInstock =  (int) Mage::helper('solrsearch')->getSetting('check_instock');
        if ($checkInstock > 0) {
            $filterQuery['instock_int'] = array(1);
        }

        $filterQueryArray = array();
        foreach($filterQuery as $key=>$filterItem){
            if(is_array($filterItem) && sizeof($filterItem) > 0){
                $query = '';
                foreach($filterItem as $value){
                    $query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22';
                    $filterQueryArray[] = $query;
                }
            }
        }

        // Vendor context - add vendor/brandshop filter according to vendor type
        $_vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if ($_vendor && $_vendor->getId()) {
            switch ($_vendor->getVendorType()) {
                case Zolago_Dropship_Model_Source::VENDOR_TYPE_BRANDSHOP:
                    $filterQueryArray[] = 'udropship_brandshop_id_int'. ':' . $_vendor->getId();
                    break;
                case Zolago_Dropship_Model_Source::VENDOR_TYPE_STANDARD:
                    $filterQueryArray[] = 'udropship_vendor_id_int'. ':' . $_vendor->getId();
                    break;
            }
        }

        $filterQueryString = '%28' . @implode('%29+AND+%28', $filterQueryArray) . '%29';
        $this->filterQuery = $filterQueryString;
    }

    public function getSortFieldByCode($attributeCode, $direction){
        return 'sort_wishlist_count_int+desc,is_bestseller_int+desc';
    }

    /**
     * @return Zolago_Solrsearch_Model_Solr_Vendor_Landingpage_Product_List
     */
    public function getListModel() {
        return Mage::getSingleton('zolagosolrsearch/solr_vendor_landingpage_product_list');
    }
}