<?php

class Zolago_Campaign_Model_Campaign extends Mage_Core_Model_Abstract
{
    const ZOLAGO_CAMPAIGN_ID_CODE = "campaign_regular_id";
    const ZOLAGO_CAMPAIGN_INFO_CODE = "campaign_info_id";

    protected function _construct()
    {
        $this->_init("zolagocampaign/campaign");
    }

    /**
     * @param array $data
     * @return boolean|array
     */
    public function validate($data = null)
    {
        if ($data === null) {
            $data = $this->getData();
        } elseif ($data instanceof Varien_Object) {
            $data = $data->getData();
        }

        if (!is_array($data)) {
            return false;
        }

        $errors = Mage::getSingleton("zolagocampaign/campaign_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
    /**
     * @return array
     */
    public function getAllowedWebsites() {
        if(!$this->hasData("website_ids")){
            $allowedWebsites = array();
            if($this->getId()){
                $allowedWebsites = $this->getResource()->getAllowedWebsites($this);
            }
            $this->setData("website_ids", $allowedWebsites);
        }
        return $this->getData("website_ids");
    }

    /**
     * @return array
     */
    public function getCampaignProducts() {
        if(!$this->hasData("campaign_products")){
            $campaignProducts = array();
            if($this->getId()){
                $campaignProducts = $this->getResource()->getCampaignProducts($this);
            }
            $this->setData("campaign_products", implode("," , $campaignProducts));
        }
        return $this->getData("campaign_products");
    }

    /*
     * @return array
     */
    public function getProductCampaign() {
        return $this->getResource()->getProductCampaign();
    }

    /*
     * @return array
     */
    public function getProductCampaignInfo() {
        return $this->getResource()->getProductCampaignInfo();
    }


    public function processCampaignAttributes(){
        //Select campaigns with expired date
        /* @var $resourceModel Zolago_Campaign_Model_Resource_Campaign */
        $resourceModel = $this->getResource();
        $notValidCampaigns = $resourceModel->getNotValidCampaigns();
//        Zend_debug::dump($notValidCampaigns);

        if(empty($notValidCampaigns)){
            return;
        }

        $localeTime = Mage::getModel('core/date')->timestamp(time());
        $localeTimeF = date("Y-m-d H:i", $localeTime);
        $dataToUpdate = array();

        foreach ($notValidCampaigns as $notValidCampaign) {
            if (!empty($notValidCampaign['date_to'])
                && $notValidCampaign['date_to'] <= $localeTimeF
            ) {
                $archiveCampaigns[$notValidCampaign['campaign_id']] = $notValidCampaign['campaign_id'];
            }
            $dataToUpdate[$notValidCampaign['type']][$notValidCampaign['campaign_id']][] = $notValidCampaign['product_id'];
        }

        //When ending date comes Campaign status goes to archive
        $collection = Mage::getModel("zolagocampaign/campaign")
            ->getCollection();
        $collection->addFieldToFilter('campaign_id', array('in', $archiveCampaigns));

        foreach ($collection as $collectionItem) {
            $collectionItem->setData('status', Zolago_Campaign_Model_Campaign_Status::TYPE_ARCHIVE);
            $collectionItem->save();
        }

        if (!empty($dataToUpdate)) {
            $actionModel = Mage::getSingleton('catalog/product_action');

//            $storeId = array(Mage_Core_Model_App::ADMIN_STORE_ID);
            $storeId = array();
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                $storeId[] = $_storeId;
            }
            $productIdsToUpdate = array();
            foreach ($dataToUpdate as $type => $campaignData) {
                //unset products campaign attributes
                foreach ($campaignData as $campaignId => $productIds) {
                    $productIdsToUpdate = array_merge($productIdsToUpdate, $productIds);
                    if ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_INFO) {
                        foreach ($storeId as $store) {
                            foreach ($productIds as $productId) {
                                $val = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ZOLAGO_CAMPAIGN_INFO_CODE, $store);
                                $campaignIds = explode(",", $val);
                                $campaignIds = array_diff($campaignIds, array($campaignId));
                                if (!empty($campaignIds)) {
                                    $attributesData = array(self::ZOLAGO_CAMPAIGN_INFO_CODE => $campaignIds);
                                    $actionModel
                                        ->updateAttributesNoIndex($productIds, $attributesData, (int)$store);
                                }
                            }
                        }

                    } elseif ($type == Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION || $type == Zolago_Campaign_Model_Campaign_Type::TYPE_SALE) {
                        $attributesData = array(self::ZOLAGO_CAMPAIGN_ID_CODE => 0);
                        foreach ($storeId as $store) {
                            $actionModel
                                ->updateAttributesNoIndex($productIds, $attributesData, (int)$store);
                        }
                    }
                }
                unset($attributesData);

                //unset special price
                //unset special price dates
                //unset SRP price
                $attributesData = array('special_price' => '', 'special_from_date' => '', 'special_to_date' => '');
                foreach ($storeId as $store) {
                    $actionModel
                        ->updateAttributesNoIndex($productIdsToUpdate, $attributesData, (int)$store);
                }
            }

            //3. reindex
            $actionModel->reindexAfterMassAttributeChange();

            //4. push to solr
            Mage::dispatchEvent(
                "catalog_converter_price_update_after",
                array(
                    "product_ids" => $productIdsToUpdate
                )
            );
        }

    }

    public function getExpiredCampaigns()
    {
        return $this->getResource()->getExpiredCampaigns();
    }
}