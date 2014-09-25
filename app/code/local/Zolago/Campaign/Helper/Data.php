<?php

class Zolago_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $string
     * @return string
     */
    function createCampaignSlug($string)
    {
        //1.Create url from name
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($string));
        $urlKey = strtolower($urlKey);
        $slug = trim($urlKey, '-');
        $slugFull = $slug . '.html';

        if(!$this->_slugExists($slugFull)){
            $result = $slugFull;
        } else {
            for ($i = 1; $i <= 10; $i++) {
                $slugFullIncrement = $slug . '-' . $i . '.html';
                if(!$this->_slugExists($slugFullIncrement)){
                    $result = $slugFullIncrement;
                    break;
                }
            }
        }
        return $result;
    }

    protected function _slugExists($slug)
    {
        $store = Mage::app()->getStore();
        $collection = Mage::getResourceModel('core/url_rewrite_collection');
        /* @var $collection Mage_Core_Model_Resource_Url_Rewrite_Collection */
        $collection->addStoreFilter($store);
        $collection->addFieldToFilter("request_path", $slug);

        $collectionCampaign = Mage::getResourceModel('zolagocampaign/campaign_collection')
            ->addFieldToFilter('url_key', $slug);
        //$collectionCampaign->printLogQuery(true);
        $slugCampaignExist = $collectionCampaign->getFirstItem()->getUrlKey();

        $slugExists = (empty($slugCampaignExist) && $collection->getSize() == 0) ? false : true;
        return $slugExists;
    }

    public function getBannerTypesSlots()
    {
        return Mage::getSingleton('zolagobanner/banner_type')->toOptionHash();
    }

    /**
     * @return array
     */
    public function getAllVendorsList()
    {
        $vendorModel = Mage::getModel('udropship/vendor');
        $vendorCollection = $vendorModel->getCollection();
        $vendorsList = array();
        foreach ($vendorCollection as $vendorObj) {
            $vendorsList[$vendorObj->getId()] = $vendorObj->getVendorName();
        }
        return $vendorsList;
    }


}