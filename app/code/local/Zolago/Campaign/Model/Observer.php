<?php

class Zolago_Campaign_Model_Observer
{

    static function setProductAttributes(){
        $actionModel = Mage::getSingleton('catalog/product_action');
        $storesToUpdate = array(Mage_Core_Model_App::ADMIN_STORE_ID);
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


        //1. Set campaign attributes
        //info campaign
        $model = Mage::getModel('zolagocampaign/campaign');
        $campaignInfo = $model->getResource()
            ->getUpDateCampaigns(array(Zolago_Campaign_Model_Campaign_Type::TYPE_INFO));

        $dataToUpdate = array();
        if (!empty($campaignInfo)) {
            foreach ($campaignInfo as $campaignInfoItem) {
                $dataToUpdate[$campaignInfoItem['product_id']][] = $campaignInfoItem['campaign_id'];
            }
            unset($campaignInfoItem);
        }

        if (!empty($dataToUpdate)) {
            foreach ($dataToUpdate as $productId => $campaignIds) {
                $attributesData = array('campaign_info_id' => implode(",", $campaignIds));
                foreach ($storesToUpdate as $store) {
                    $actionModel
                        ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                }
            }
        }
        unset($dataToUpdate);

        //sales/promo campaign
        $model = Mage::getModel('zolagocampaign/campaign');
        $campaignSalesPromo = $model->getResource()
            ->getUpDateCampaigns(
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
                    'special_from_date' => date('Y-m-d', strtotime($data['date_from'])),
                    'special_to_date' => date('Y-m-d', strtotime($data['date_to'])),
                    'msrp' => $data['price_srp']
                );
                foreach ($storesToUpdate as $store) {
                    $actionModel
                        ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                }
                unset($store);
                $priceTypeSource[$data['product_id']] = $data['price_source'];
            }
        }
        //2. Set special price
        $productIds = array_keys($dataToUpdate);

        $queueModel = Mage::getResourceModel('zolagocatalog/queue_pricetype');
        $skuvS = $queueModel->getVendorSkuAssoc($productIds);

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
        }

        //Special Price
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
                            $newPriceWithPercent = $newPrice + $newPrice * ((int)$percent / 100);
                            Mage::log('newPriceWithPercent ' . $newPriceWithPercent);
                            $attributesData = array('special_price' => $newPriceWithPercent);
                            $actionModel
                                ->updateAttributesNoIndex(array($productId), $attributesData, $store);
                        }
                        unset($store);
                    }
                }

            }
        }

        Mage::getSingleton('catalog/product_action')
            ->reindexAfterMassAttributeChange();
    }
    static public function processCampaignAttributes()
    {
        Mage::log(microtime() . " Starting processCampaignAttributes ", 0, 'processCampaignAttributes.log');
        Mage::getModel("zolagocampaign/campaign")->processCampaignAttributes();
    }
}