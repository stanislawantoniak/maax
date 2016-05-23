<?php

/**
 * Import products
 */

require_once(MAGENTO_ROOT . DS . "magmi/inc/magmi_defs.php");
//Datapump include
require_once(MAGENTO_ROOT . DS . "magmi/integration/inc/magmi_datapump.php");

class ZolagoOs_Import_Model_Import_Product
    extends ZolagoOs_Import_Model_Import
{
    public function runImport()
    {
        $this->_import();
    }


    protected function _import()
    {

        $vendorId = $this->getExternalId();
        if (empty($vendorId)) {
            $this->log("CONFIGURATION ERROR: EMPTY VENDOR ID", Zend_Log::ERR);
            return $this;
        }

        //1. Read file
        $fileName = $this->_getPath();
        $this->log("READING FILE {$fileName}");
        if (empty($fileName)) {
            $this->log("CONFIGURATION ERROR: EMPTY PRODUCT IMPORT FILE", Zend_Log::ERR);
            return $this;
        }

        if (!file_exists($fileName)) {
            $this->log("CONFIGURATION ERROR: IMPORT FILE {$fileName} NOT FOUND", Zend_Log::ERR);
            return $this;
        }
        try {
            $fileContent = file_get_contents($fileName);
            $xml = simplexml_load_string($fileContent);

            //2. Import products
            $xmlToArray = (array)$xml;
            if (empty($xmlToArray)) {
                $this->log("No Data For Import", Zend_Log::ALERT);
                return $this;
            }


            //Collect sku
            $skuBatch = array();
            foreach ($xmlToArray["item"] as $productXML) {
                $skuBatch[explode("/", (string)$productXML->sku)[0]][] = $productXML;
            }

            if (empty($skuBatch)) {
                $this->log("No Data For Import", Zend_Log::ALERT);
                return $this;
            }


            // create a Product import Datapump using Magmi_DatapumpFactory
            $dp = Magmi_DataPumpFactory::getDataPumpInstance("productimport");


            // Begin import session with a profile & running mode,
            // here profile is "default" & running mode is "create".
            // Available modes:
            // "create" creates and updates items,
            // "update" updates only,
            // "xcreate creates only.
            // Important: for values other than "default" profile has to be an existing magmi profile
            $importProfile = "dev_01";
            $dp->beginImportSession($importProfile, "xcreate", new ZolagoOs_Import_Model_ImportProductsLogger());

            $skusUpdated = array();
            foreach ($skuBatch as $configurableSkuv => $simples) {
                $u = $this->insertConfigurable($dp, $vendorId, $configurableSkuv, $simples);
                $skusUpdated = array_merge($skusUpdated, $u);
            }

            /* end import session, will run post import plugins */
            $dp->endImportSession();


            //3. Set additional attributes

            //Update udropship_vendor
            // MAGMI: warning:Potential assignment problem, specific model found for select attribute => udropship_vendor(udropship/vendor_source)

            /* @var $collectionConfigurable Mage_Catalog_Model_Resource_Product_Collection */
            $collectionConfigurable = Mage::getResourceModel('zolagocatalog/product_collection');
            $collectionConfigurable->addFieldToFilter("sku", array("in" => $skusUpdated));
            $collectionConfigurable->addFieldToFilter("type_id", Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
            $idsConfigurable = $collectionConfigurable->getAllIds();


            /* @var $collectionConfigurable Mage_Catalog_Model_Resource_Product_Collection */
            $collectionSimple = Mage::getResourceModel('zolagocatalog/product_collection');
            $collectionSimple->addFieldToFilter("sku", array("in" => $skusUpdated));
            $collectionSimple->addFieldToFilter("type_id", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
            $idsSimple = $collectionSimple->getAllIds();


            $ids = array_merge($idsConfigurable, $idsSimple);
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');

            //3a. Set additional attributes (udropship_vendor for all products)
            $aM->updateAttributesPure($ids, array('udropship_vendor' => $vendorId), 0);

            //3b. Set additional attributes (status opisu = niezatwierdzony for all products)
            $aM->updateAttributesPure($ids, array('description_status' => 1), 0);


        } catch (Exception $e) {
            Mage::logException($e);
        }

    }


    /**
     * @param $dp
     * @param $vendorId
     * @param $configurableSkuv
     * @param $simples
     * @return array
     */
    public function insertConfigurable($dp, $vendorId, $configurableSkuv, $simples)
    {
        $attributeSet = "Default";

        $skusUpdated = [];
        $subskus = array();
        foreach ($simples as $simpleXMLData) {
            $simpleSkuV = (string)$simpleXMLData->sku;
            $simpleSku = $vendorId . "-" . $simpleSkuV;
            $subskus[] = $simpleSku;  //Collect simple skus for configurable
            $product = array(
                "name" => $simpleXMLData->description,
                "sku" => $simpleSku,
                "skuv" => $simpleSkuV,
                "price" => "0.00",
                "type" => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
                "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
                "tax_class_id" => 0,
                "attribute_set" => $attributeSet,
                "store" => "admin",
                "description" => $simpleXMLData->clothes_description,
                "short_description" => $simpleXMLData->description2,
                "size" => $simpleXMLData->size,
            );
            // Now ingest item into magento
            $dp->ingest($product);
            $skusUpdated[] = $simpleSku;
        }
        $this->log($skusUpdated);
        //Create configurable
        $firstSimple = $simples[0];
        $configurableSku = $vendorId . "-" . $configurableSkuv;
        $productConfigurable = array(
            "name" => $simpleXMLData->description,
            "sku" => $configurableSku,
            "skuv" => $configurableSkuv,
            "price" => "0.00",
            "type" => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
            "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            "tax_class_id" => 0,
            "attribute_set" => $attributeSet,
            "store" => "admin",
            "configurable_attributes" => "size",
            "simples_skus" => implode(",", $subskus),

            "description" => $firstSimple->clothes_description,
            "short_description" => $firstSimple->description2,

            //ext_
            "ext_productline" => $firstSimple->collection,
            "ext_category" => $firstSimple->clothes_description,
            "ext_color" => $firstSimple->color,
            "ext_brand" => $firstSimple->brand,

        );

        //Additional columns
        $additionalColumns = array(
            "gender", "intake", "clothes_type", "size_group", "week_no", "barcode"
        );
        for ($n = 0; $n < count($additionalColumns); $n++) {
            $property = $additionalColumns[$n];
            if (!empty($propertyValue = $this->formatAdditionalColumns($firstSimple, $property)))
                $productConfigurable["col" . ($n + 1)] = $propertyValue;
        }

        // Now ingest item into magento
        $dp->ingest($productConfigurable);
        $skusUpdated[] = $configurableSku;

        return $skusUpdated;
    }

    /**
     * @param $col
     * @param $item
     * @return string
     */
    public function formatAdditionalColumns($col, $item)
    {

        $result = "";
        if (!property_exists($col, $item)) {
            return $result;
        }
        if (empty((string)$col->$item)) {
            return $result;
        }
        $result = "{$item}: " . (string)$col->$item;

        return $result;
    }

    public function log($message, $level = NULL)
    {
        Mage::log($message, $level, "zolagoosimport_product.log");
    }
}
