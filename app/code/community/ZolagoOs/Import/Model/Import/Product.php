<?php

/**
 * Import products
 */

require_once(Mage::getBaseDir() . DS . "magmi/inc/magmi_defs.php");
//Datapump include
require_once(Mage::getBaseDir() . DS . "magmi/integration/inc/magmi_datapump.php");

class ZolagoOs_Import_Model_Import_Product
    extends ZolagoOs_Import_Model_Import
{
    protected $_simpleSkus = [];
    protected $_configurableSkus = [];
    protected $_vendor;

    const MAGMI_IMPORT_PROFILE = "wojcik";

    public function __construct()
    {
        $this->_vendor = $this->getExternalId();
    }

    /**
     * @return array
     */
    public function getVendorId()
    {
        return $this->_vendor;
    }

    /**
     * @return array
     */
    public function getProductSkus()
    {
        return array_merge($this->_simpleSkus, $this->_configurableSkus);
    }


    /**
     * @return mixed
     */
    public function getSimpleSkus()
    {
        return $this->_simpleSkus;
    }

    /**
     * @param mixed $simpleSkus
     */
    public function setSimpleSkus($simpleSkus)
    {
        $this->_simpleSkus = array_merge($this->_simpleSkus, array($simpleSkus));
    }


    /**
     * @return mixed
     */
    public function getConfigurableSkus()
    {
        return $this->_configurableSkus;
    }

    /**
     * @param mixed $configurableSkus
     */
    public function setConfigurableSkus($configurableSkus)
    {
        $this->_configurableSkus = array_merge($this->_configurableSkus, array($configurableSkus));
    }


    public function runImport()
    {
        $this->_import();
    }


    protected function _import()
    {

        $vendorId = $this->getVendorId();
        if (empty($vendorId)) {
            $this->log("CONFIGURATION ERROR: EMPTY VENDOR ID", Zend_Log::ERR);
            return $this;
        }

        //1. Read file
        $fileName = $this->_getPath();
        //$this->log("READING FILE {$fileName}");
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
            if (is_array($xmlToArray["item"])) {
                foreach ($xmlToArray["item"] as $productXML) {
                    $skuBatch[explode("/", (string)$productXML->sku)[0]][(string)$productXML->sku] = $productXML;

                }
            }
//            if (is_object($xmlToArray["item"])) {
//                $productXML = $xmlToArray["item"];
//                $skuBatch[explode("/", (string)$productXML->sku)[0]][(string)$productXML->sku] = $productXML;
//            }



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
            $skusCreated = [];
            $importProfile = self::MAGMI_IMPORT_PROFILE;
            $dp->beginImportSession($importProfile, "xcreate", new ZolagoOs_Import_Model_ImportProductsLogger());
            foreach ($skuBatch as $configurableSkuv => $simples) {
                $u = $this->insertConfigurable($dp, $vendorId, $configurableSkuv, $simples);
                $skusCreated = array_merge($u, $skusCreated);
            }
            /* end import session, will run post import plugins */
            $dp->endImportSession();


            //Start update configurable with children session
//            $dp->beginImportSession($importProfile, "update", new ZolagoOs_Import_Model_ImportProductsLogger());
//            $this->updateRelations($dp,$skusCreated);
//            $dp->endImportSession();


            //3. Set additional attributes
            $this->updateAdditionalAttributes();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function updateAdditionalAttributes()
    {
        $vendorId = $this->_vendor;
        $skusUpdated = $this->getProductSkus();
        $skusConfigurableUpdated = $this->getConfigurableSkus();
        //Update udropship_vendor
        // MAGMI: warning:Potential assignment problem, specific model
        // found for select attribute => udropship_vendor(udropship/vendor_source)

        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addFieldToFilter("sku", array("in" => $skusUpdated));
        $ids = $collection->getAllIds();

        $collection2 = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection2->addFieldToFilter("sku", array("in" => $skusConfigurableUpdated));
        $idsConfigurable = $collection2->getAllIds();

        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        //3a. Set additional attributes (udropship_vendor for all products)
        //3b. Set additional attributes (status opisu = niezatwierdzony for all products)
        $aM->updateAttributesPure(
            $ids,
                array(
                    'udropship_vendor' => $vendorId,
                    'description_status' => 1,
                   // 'manufacturer' => $vendorId
                ),
            0);

        //3c. Set additional attributes (status brandshop for configurable products)
        $aM->updateAttributesPure($idsConfigurable, array('brandshop' => $vendorId), 0);

    }

    public function updateRelations($dp,$skusCreated)
    {
        $configurableSkus = array_keys($skusCreated);

        $productResource = Mage::getResourceModel("zolagocatalog/product");

        $readConnection = $productResource->getReadConnection();
        $catalogProductSuperLink = $readConnection->getTableName('catalog_product_super_link');
        $catalogProductEntity = $readConnection->getTableName('catalog_product_entity');
        $select = $readConnection->select();
        $select->from(
            array("catalog_product_super_link" => $catalogProductSuperLink),
            array(
                "parent_id" => "parent_id",
                "child_id"      => "product_id",
            )
        );
        $select->join(
            array("e_parent" => $catalogProductEntity),
            "e_parent.entity_id=catalog_product_super_link.parent_id",
            array("parent_sku"       => "e_parent.sku")
        );
        $select->join(
            array("e_child" => $catalogProductEntity),
            "e_child.entity_id=catalog_product_super_link.product_id",
            array("child_sku" 	  => "e_child.sku")
        );
        $select->where("e_parent.sku IN(?)", $configurableSkus);

        try {
            $assoc = $readConnection->fetchAll($select);
            if(empty($assoc))
                return;
            $children = [];
            foreach($assoc as $assocItem){
                $children[$assocItem["parent_sku"]][] = $assocItem["child_sku"];
            }

            foreach ($skusCreated as $skuConfigurable => $skusSimple) {
                if(isset($children[$skuConfigurable])){
                    $skusSimple = array_merge($skusSimple, $children[$skuConfigurable]);
                }
                $dp->ingest(
                    array(
                        "sku" => $skuConfigurable,
                        "type" => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                        "configurable_attributes" => "size",
                        "simples_skus" => implode(",", $skusSimple),
                        "options_container" => array()
                    )
                );
            }
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
        $subskus = [];
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
                "tax_class_id" => 2,
                "attribute_set" => $attributeSet,
                "store" => "admin",
                "description" => $simpleXMLData->clothes_description,
                "short_description" => $simpleXMLData->description2,
                "size" => $simpleXMLData->size,
                "ean" => $simpleXMLData->barcode,            


                //magazyn dla prostych - zarządzaj stanami tak, ilość 0, dostępność - brak w magazynie
                "manage_stock" => 1,
                "qty" => 0,
                "is_in_stock" => 0
            );
            // Now ingest item into magento
            $simpleResult =$dp->ingest($product);


            if($simpleResult["ok"]){
                $this->setSimpleSkus($simpleSku);
            }

            unset($simpleXMLData,$product);
        }



        //Create configurable
        $firstSimple = array_values($simples)[0];

        $configurableSku = $vendorId . "-" . $configurableSkuv;
        $productConfigurable = array(
            "name" => (string)$firstSimple->description,
            "sku" => $configurableSku,
            "skuv" => $configurableSkuv,
            "price" => "0.00",
            "type" => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
            "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            "tax_class_id" => 2,
            "attribute_set" => $attributeSet,
            "store" => "admin",
            "configurable_attributes" => "size",
            "simples_skus" => implode(",", $subskus),

            "description" => (string)$firstSimple->clothes_description,
            "short_description" => (string)$firstSimple->description2,
            "ean"	=> (string)$firstSimple->barcode,

            //ext_
            "ext_productline" => (string)$firstSimple->collection,
            "ext_category" => (string)$firstSimple->clothes_description,
            "ext_color" => (string)$firstSimple->color,
            "ext_brand" => (string)$firstSimple->brand,

            "col1" => "Kolekcja:" . (string)$firstSimple->description2,

            //magazyn dla konfigurowalnych - zarządzaj stanami = nie
            "use_config_manage_stock" => 0,
            "manage_stock" => 0,
            "qty" => 0,
            "is_in_stock" => 0

        );

        //Additional columns
        $additionalColumns = array(
            "gender", "intake", "clothes_type", "size_group", "week_no", "barcode"
        );
        for ($n = 1; $n < count($additionalColumns); $n++) {
            $property = $additionalColumns[$n];
            if (!empty($propertyValue = $this->formatAdditionalColumns($firstSimple, $property)))
                $productConfigurable["col" . ($n + 1)] = $propertyValue;
        }

        // Now ingest item into magento
        $configurableResult = $dp->ingest($productConfigurable);
        if($configurableResult["ok"]){
            $skusUpdated[$configurableSku] = $subskus;
            $this->setConfigurableSkus($configurableSku);
        }

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
        $result = "{$item}:" . (string)$col->$item;

        return $result;
    }

    public function log($message, $level = NULL)
    {
        Mage::log($message, $level, "zolagoosimport_product.log");
    }
}
