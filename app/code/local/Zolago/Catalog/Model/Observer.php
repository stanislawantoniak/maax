<?php
class Zolago_Catalog_Model_Observer {

    public function recalcConfigurable()
    {

        $base_path = Mage::getBaseDir('base');

        $hash = md5(microtime());

        //read dir
        $dir = $base_path . '/var/log/configurableUpdate';
        $dh = opendir($dir);

        if(!$dh){
            Mage::log("No updates ", 0, 'configurable_update.log');
            return;
        }

        while (false !== ($filename = readdir($dh))) {
            if (substr($filename, 0, 13) === 'configurable_') {
                $files[$filename] = filemtime($dir . "/" . $filename);
            }
            asort($files);
        }
        closedir($dh);

        if (empty($files)) {
            Mage::log("No updates ", 0, 'configurable_update.log');
            return;
        }
        //get first file in queue
        $fileName = array_keys($files)[0];
        $configurableFile = $base_path . '/var/log/configurableUpdate/' . $fileName;
        $configurableFileData = file_get_contents($configurableFile);

        $date = str_replace(array('configurable_','.txt'),'',$fileName);



        if (!$configurableFileData) {
            Mage::log($hash . " No updates ", 0, 'configurable_update.log');
            return;
        }

        $resource = Mage::getSingleton('core/resource');
        $zolagoCatalogModelProductConfigurableData = Mage::getModel('zolagocatalog/product_configurable_data');

        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        Mage::log("{$hash} {$date} Start ", 0, 'configurable_update.log');
        $configurableData = explode(',', $configurableFileData);
        if (empty($configurableData)) {
            Mage::log($hash . " Empty info ", 0, 'configurable_update.log');
            return;
        }
        $storeId = $configurableData[0];
        $websiteId = $configurableData[0];

        unset($configurableData[0]);

        $listUpdatedProducts = implode(',', $configurableData);


        //define parent products (configurable) by child (simple)
        $configurableSimpleRelation = $zolagoCatalogModelProductConfigurableData->getConfigurableSimpleRelation($listUpdatedProducts);
        if (empty($configurableSimpleRelation)) {
            Mage::log("{$hash} {$date} Found 0 configurable products ", 0, 'configurable_update.log');
            return;
        }
        $relations = count($configurableSimpleRelation);
        Mage::log(" define parent products (configurable) by child (simple) ", 0, 'configurable_update.log');
        $configurableProductsIds = implode(',', array_keys($configurableSimpleRelation));


        //min prices
        $minPrices = $zolagoCatalogModelProductConfigurableData->getConfigurableMinPrice($storeId, $configurableProductsIds);
        //--min prices
        Mage::log($hash . " min prices ", 0, 'configurable_update.log');

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
        Mage::log("{$hash} {$date} super attribute ids ", 0, 'configurable_update.log');


        $productAction = Mage::getSingleton('catalog/product_action');
        $productConfigurableIds = array();
        Mage::log("{$hash} {$date} {$relations} relations found ", 0, 'configurable_update.log');
        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {
            $productMinPrice = isset($minPrices[$productConfigurableId]) ? $minPrices[$productConfigurableId]['min_price'] : FALSE;

            //update configurable product price
            if ($productMinPrice)
                $productAction->updateAttributes(array($productConfigurableId), array('price' => $productMinPrice), $storeId);

            Mage::log("{$hash} {$date} update configurable product price ", 0, 'configurable_update.log');

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
                        Mage::log("{$hash} {$date} insert ", 0, 'configurable_update.log');
                    }
                }
                $productConfigurableIds[] = $productConfigurableId;
            }

        }
        $countUpdated = count($productConfigurableIds);
        Mage::log("{$hash} {$date} Configurable({$countUpdated}) " . implode(',', $productConfigurableIds), 0, 'configurable_update.log');


        Mage::log("{$hash} {$date} Reindex ", 0, 'configurable_update.log');
        $indexProcessModel = Mage::getModel('index/process');

        $index = array(1, 2, 4, 5, 8);
        foreach ($index as $i) {
            $process = $indexProcessModel->load($i);
            $process->reindexAll();
        }
        Mage::log("{$hash} {$date} End ", 0, 'configurable_update.log');
        if (file_exists($configurableFile)) {
            unlink($configurableFile);
        }
    }

}