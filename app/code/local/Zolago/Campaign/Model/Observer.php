<?php

class Zolago_Campaign_Model_Observer
{

    static function setProductAttributes(){
        $actionModel = Mage::getSingleton('catalog/product_action');

        $storeId = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $storeId[] = $_storeId;
        }

        $model = Mage::getModel('zolagocampaign/campaign');
        $campaignProducts = $model->getResource()->getUpDateCampaigns();
        $data = array();
        if (!empty($campaignProducts)) {
            foreach ($campaignProducts as $campaignProductsItem) {
                $data[$campaignProductsItem['type']][] = $campaignProductsItem;
            }
        }

        $info = isset($data['info'])?  $data['info']: array();
        $sale = isset($data['sale'])?  $data['sale']: array();
        $promotion = isset($data['promotion'])?  $data['promotion']: array();
        $dataToUpdate = array();
        if (!empty($info)) {
            foreach ($info as $infoItem) {
                $dataToUpdate[$infoItem['product_id']][] = $infoItem['campaign_id'];
            }
        }
        if (empty($dataToUpdate)) {
            foreach ($dataToUpdate as $productId => $campaignIds) {
                $attributesData = array('campaign_info_id' => implode(",", $campaignIds));
                foreach ($storeId as $store) {
                    $actionModel
                        ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                }
            }
        }


        $regular = array_merge($sale, $promotion);

        $dataToUpdate = array();
        if (!empty($regular)) {
            foreach ($regular as $regularItem) {
                $dataToUpdate[$regularItem['product_id']] = $regularItem['campaign_id'];
            }
        }
        if (empty($dataToUpdate)) {
            foreach ($dataToUpdate as $productId => $campaignId) {
                $attributesData = array('campaign_regular_id' => $campaignId);
                foreach ($storeId as $store) {
                    $actionModel
                        ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                }
            }
        }




//        Mage::getSingleton('catalog/product_action')
//            ->reindexAfterMassAttributeChange();
    }
    static public function processCampaignAttributes()
    {
        Mage::log(microtime() . " Starting processCampaignAttributes ", 0, 'processCampaignAttributes.log');
        Mage::getModel("zolagocampaign/campaign")->processCampaignAttributes();
    }
}