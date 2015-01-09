<?php

class Zolago_Campaign_Model_Observer
{

    static function setProductAttributes()
    {
        /* @var $actionModel Zolago_Catalog_Model_Product_Action */
        $actionModel = Mage::getSingleton('catalog/product_action');

        $storesToUpdate = array();
        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $storesToUpdate[] = $store->getId();
                }
                unset($store);
            }
            unset($group);
        }


        $productIds = array();
        /* @var $model Zolago_Campaign_Model_Resource_Campaign */
        $model = Mage::getResourceModel('zolagocampaign/campaign');

        //1. Set campaign attributes
        //info campaign
        $campaignInfo = $model->getUpDateCampaigns(array(Zolago_Campaign_Model_Campaign_Type::TYPE_INFO));

        $dataToUpdate = array();
        if (!empty($campaignInfo)) {
            foreach ($campaignInfo as $campaignInfoItem) {
                $dataToUpdate[$campaignInfoItem['product_id']][] = $campaignInfoItem['campaign_id'];
            }
            unset($campaignInfoItem);
        }


        if (!empty($dataToUpdate)) {
            foreach ($dataToUpdate as $productId => $campaignIds) {
                if (!empty($campaignIds)) {
                    $attributesData = array(
                        Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_INFO_CODE => implode(",", $campaignIds)
                    );
                    foreach ($storesToUpdate as $store) {
                        $actionModel
                            ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                    }
                    $productIds[$productId] = $productId;
                }
            }
            unset($productId);
        }

        unset($dataToUpdate);
        unset($attributesData);


        //sales/promo campaign
        $salesPromoProductIds = array(); //collect product ids attached to SALE and PROMOTION campaigns
        $campaignSalesPromo = $model->getUpDateCampaigns(
            array(
                Zolago_Campaign_Model_Campaign_Type::TYPE_SALE,
                Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
            )
        );


        $dataToUpdate = array();
        if (!empty($campaignSalesPromo)) {
            foreach ($campaignSalesPromo as $campaignSalesPromoItem) {
                if(isset($dataToUpdate[$campaignSalesPromoItem['product_id']])){
                    //get one last updated campaign
                    continue;
                }
                $dataToUpdate[$campaignSalesPromoItem['product_id']] = $campaignSalesPromoItem;
            }
            unset($campaignSalesPromoItem);
        }

        $salesPromoProductsData = array();
        $priceTypeSource = array();
        if (!empty($dataToUpdate)) {
            foreach ($dataToUpdate as $productId => $data) {
                krumo($data);
                $attributesData = array(
                    'campaign_regular_id' => $data['campaign_id'],
                    'special_from_date' => !empty($data['date_from']) ? date('Y-m-d', strtotime($data['date_from'])) : '',
                    'special_to_date' => !empty($data['date_to']) ? date('Y-m-d', strtotime($data['date_to'])) : ''
                );

                foreach ($storesToUpdate as $store) {
                    $actionModel
                        ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                }
                unset($store);
                $priceTypeSource[$data['product_id']] = $data['price_source'];
                $salesPromoProductsData[$data['website_id']][$productId] = array(
                    'price_source' => $data['price_source'],
                    'price_percent' => $data['price_percent'],
                    'website_id' => $data['website_id']
                );
            }
            unset($productId);
        }
        //2. Set options


        /* @var $modelCampaign Zolago_Campaign_Model_Campaign */
        $modelCampaign = Mage::getModel('zolagocampaign/campaign');
        foreach ($salesPromoProductsData as $websiteId => $salesPromoProductsDataH) {
            $modelCampaign->setProductOptionsByCampaign($salesPromoProductsDataH, $websiteId);
        }


//
//        //3. reindex
//        $actionModel->reindexAfterMassAttributeChange();
//
//        //4. push to solr
//        Mage::dispatchEvent(
//            "catalog_converter_price_update_after",
//            array(
//                "product_ids" => $productIds
//            )
//        );
    }

    static public function processCampaignAttributes()
    {
        /* @var $campaignModel Zolago_Campaign_Model_Campaign */
        $campaignModel = Mage::getModel("zolagocampaign/campaign");
        $campaignModel->processCampaignAttributes();
    }
}