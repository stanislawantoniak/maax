<?php

class Zolago_Campaign_Model_Observer
{

    static function setProductAttributes(){
        /* @var $actionModel Zolago_Catalog_Model_Product_Action */
        $actionModel = Mage::getSingleton('catalog/product_action');
//        $storesToUpdate = array(Mage_Core_Model_App::ADMIN_STORE_ID);
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
        $campaignSalesPromo = $model->getUpDateCampaigns(
            array(
                Zolago_Campaign_Model_Campaign_Type::TYPE_SALE,
                Zolago_Campaign_Model_Campaign_Type::TYPE_PROMOTION
            )
        );


        $dataToUpdate = array();
        if (!empty($campaignSalesPromo)) {
            foreach ($campaignSalesPromo as $campaignSalesPromoItem) {
                $dataToUpdate[$campaignSalesPromoItem['product_id']] = $campaignSalesPromoItem;
            }
            unset($campaignSalesPromoItem);
        }


        $priceTypeSource = array();
        if (!empty($dataToUpdate)) {
            foreach ($dataToUpdate as $productId => $data) {
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
                $productIds[$productId] = $productId;
            }
            unset($productId);
        }
        //2. Set special price
        $skuvS = $model->getVendorSkuAssoc($productIds);

        //Ping converter to get special price
        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable: check credentials");
            return;
        }
        $attr = Mage::getResourceModel('catalog/product')
            ->getAttribute(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);

        if (!empty($priceTypeSource)) {
            foreach ($priceTypeSource as $productId => $priceSourceId) {
                $priceType[$productId] = $attr->getSource()->getOptionText($priceSourceId);
            }
            unset($productId);
        }

        //set Special Price
        foreach ($dataToUpdate as $productId => $data) {

            $vendorSku = isset($skuvS[$productId]) ? $skuvS[$productId]['skuv'] : FALSE;

            if ($vendorSku) {
                $sku = $skuvS[$productId]['sku'];
                $res = explode('-', $sku);
                $vendorExternalId = (!empty($res) && isset($res[0])) ? (int)$res[0] : false;
                if ($vendorExternalId) {
                    foreach ($storesToUpdate as $store) {
                        $percent = $data['price_percent'];

                        $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType[$productId]);
                        if (!empty($newPrice)) {
                            $newPriceWithPercent = $newPrice - $newPrice * ((int)$percent / 100);

                            $attributesData = array('special_price' => $newPriceWithPercent);

                            $actionModel
                                ->updateAttributesNoIndex(array($productId), $attributesData, $store);

                            $productIds[$productId] = $productId;
                        }
                        unset($store);
                    }
                }

            }
        }
        unset($productId);

        //3. reindex
        $actionModel->reindexAfterMassAttributeChange();
        Mage::log($productIds);

        //4. push to solr
        Mage::dispatchEvent(
            "catalog_converter_price_update_after",
            array(
                "product_ids" => $productIds
            )
        );
    }
    static public function processCampaignAttributes()
    {
        Mage::log(microtime() . " Starting processCampaignAttributes ", 0, 'processCampaignAttributes.log');

        Mage::getModel("zolagocampaign/campaign")->processCampaignAttributes();
    }
}