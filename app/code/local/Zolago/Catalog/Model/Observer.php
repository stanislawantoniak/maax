<?php
class Zolago_Catalog_Model_Observer {

    public function recalcConfigurable()
    {

        $base_path = Mage::getBaseDir('base');

        $hash = md5(microtime());

        //read dir
        $dir = $base_path . '/var/log/configurableUpdate';
        $dh = opendir($dir);
        while (false !== ($filename = readdir($dh))) {

            if (substr($filename, 0, 13) === 'configurable_') {
                $files[$filename] = filemtime($dir . "/" . $filename);
            }
            asort($files);
        }

        if (empty($files)) {
            Mage::log("No updates ", 0, 'configurable_update.log');
            return;
        }
        //get first file in queue
        $configurableFile = $base_path . '/var/log/configurableUpdate/' . array_keys($files)[0];
        $configurableFileData = file_get_contents($configurableFile);

        $storeId = 1;
        $websiteId = 1;

        if (!$configurableFileData) {
            Mage::log($hash . " No updates ", 0, 'configurable_update.log');
            return;
        }

        $resource = Mage::getSingleton('core/resource');
        $zolagoCatalogModelProductConfigurableData = Mage::getModel('zolagocatalog/product_configurable_data');

        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        Mage::log($hash . " Start ", 0, 'configurable_update.log');
        $configurableData = explode(',', $configurableFileData);
        $listUpdatedProducts = implode(',', $configurableData);


        //define parent products (configurable) by child (simple)
        $configurableSimpleRelation = $zolagoCatalogModelProductConfigurableData->getConfigurableSimpleRelation($listUpdatedProducts);
        if (empty($configurableSimpleRelation)) {
            Mage::log($hash . " Found 0 configurable products ", 0, 'configurable_update.log');
            return;
        }

        $configurableProductsIds = implode(',', array_keys($configurableSimpleRelation));


        //min prices
        $minPrices = $zolagoCatalogModelProductConfigurableData->getConfigurableMinPrice($storeId, $configurableProductsIds);
        //--min prices


        //super attribute ids
        $select = $readConnection->select()
            ->from('catalog_product_super_attribute',
                array('configurable_product' => 'product_id',
                    'super_attribute' => 'product_super_attribute_id'
                )
            );
        $superAttributes = $readConnection->fetchAssoc($select);
        unset($select);
        //--super attribute ids


        $productAction = Mage::getSingleton('catalog/product_action');
        $productConfigurableIds = array();
        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {
            $productMinPrice = isset($minPrices[$productConfigurableId]) ? $minPrices[$productConfigurableId]['min_price'] : FALSE;

            //update configurable product price
            if ($productMinPrice)
                $productAction->updateAttributes(array($productConfigurableId), array('price' => $productMinPrice), $storeId);

            $superAttributeId = isset($superAttributes[$productConfigurableId]) ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : FALSE;

            if ($superAttributeId) {
                $select = $readConnection->select()
                    ->from('vw_product_relation_prices_sizes')
                    ->where('parent=?', $productConfigurableId)
                    ->where('store=?', (int)$storeId);
                $productRelations = $readConnection->fetchAll($select);
                unset($select);

                if (!empty($productRelations)) {
                    $insert = array();
                    foreach ($productRelations as $productRelation) {

                        $size = $productRelation['child_size'];
                        $price = $productRelation['child_price'];

                        $priceIncrement = (float)$price - $productMinPrice;

                        $insert[] = "({$superAttributeId},{$size},{$priceIncrement},{$websiteId})";
                    }
                    if (!empty($insert)) {
                        $lineQuery = implode(",", $insert);

                        $catalogProductSuperAttributePricingTable = 'catalog_product_super_attribute_pricing';

                        $insertQuery = sprintf("
INSERT INTO  %s (product_super_attribute_id,value_index,pricing_value,website_id)
VALUES %s
ON DUPLICATE KEY UPDATE catalog_product_super_attribute_pricing.pricing_value=VALUES(catalog_product_super_attribute_pricing.pricing_value)
", $catalogProductSuperAttributePricingTable, $lineQuery);

                        $writeConnection->query($insertQuery);
                    }
                }
                $productConfigurableIds[] = $productConfigurableId;
            }

        }
        Mage::log($hash . " Configurable " . implode(',', $productConfigurableIds), 0, 'configurable_update.log');


        Mage::log($hash . " Reindex ", 0, 'configurable_update.log');
        $indexProcessModel = Mage::getModel('index/process');

        $index = array(1, 2, 4, 5, 8);
        foreach ($index as $i) {
            $process = $indexProcessModel->load($i);
            $process->reindexAll();
        }
        Mage::log($hash . " End ", 0, 'configurable_update.log');
        if (file_exists($configurableFile)) {
            unlink($configurableFile);
        }
    }

}