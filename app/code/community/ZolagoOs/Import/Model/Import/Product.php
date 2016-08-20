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
                    $skuBatch[explode("/", (string)$productXML->sku)[0]][$vendorId . "-" . (string)$productXML->sku] = $productXML;
                }
            }
            if (is_object($xmlToArray["item"])) {
                $productXML = $xmlToArray["item"];
                $skuBatch[explode("/", (string)$productXML->sku)[0]][$vendorId . "-" . (string)$productXML->sku] = $productXML;
            }

            if (empty($skuBatch)) {
                $this->log("No Data For Import", Zend_Log::ALERT);
                return $this;
            }
krumo($skuBatch);

            //create csv
            $listSimples = array(
                array(
                    "is_in_magento",
                    "skuv",
                    "sku",
                    "parentSKU",
                    "type",
                    "size",
                    "attribute_set",
                    "name",
                    "tax_class_id",
                    "ean",
                    "price",
                    "is_in_stock",
                    "configurable_attributes",
                    "simples_skus",
                    "status",
                    "visibility",
                    "qty" ,
                )
            );
            $listConfigurable = array(
                array(
                    "is_in_magento",
                    "skuv",
                    "sku",
                    "type",
                    "simples_skus",
                    "attribute_set",
                    "configurable_attributes",
                    "price",
                    "name",
                    "visibility",
                    "tax_class_id",
                    "ext_brand",
                    "ext_color",
                    "ext_productline",
                    "ext_category",
                    "ean",

                    "col1","col2","col3","col4","col5","col6","col7",


                    //magazyn dla konfigurowalnych - zarządzaj stanami = nie
                    "use_config_manage_stock",
                    "manage_stock",
                    "qty",
                    "is_in_stock",


                    "re_skus",


                )
            );
            $simpleSku = array(); $configurableSku = array();
            foreach ($skuBatch as $skuConfigurable => $childrenProducts){
                $firstSimple = array_values($childrenProducts)[0];

                foreach ($childrenProducts as $simpleXMLData){
                    $listSimples[] =
                        array(
                            0,
                            (string)$simpleXMLData->sku,
                            $vendorId . "-" . (string)$simpleXMLData->sku,
                            $skuConfigurable,
                            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                            (string)$simpleXMLData->size,
                            "Default",
                            (string)$simpleXMLData->description,
                            "", //tax_class_id
                            (string)$simpleXMLData->barcode, //ean
                            0, //price
                            0, //is_in_stock
                            "size", //configurable_attributes
                            "", //simples_skus
                            Mage_Catalog_Model_Product_Status::STATUS_DISABLED, //status
                            Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE, //visibility
                            0, //"qty"

                        );
                    $simpleSku[] = $vendorId . "-" . (string)$simpleXMLData->sku;
                }

                $listConfigurable[] =
                    array(
                        0,
                        $skuConfigurable, //skuv
                        $vendorId . "-" . $skuConfigurable, //sku
                        Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, //type
                        implode(",", array_keys($childrenProducts)), //simples_skus
                        "Default", //attribute_set
                        "size", //configurable_attributes
                        0, //price
                        (string)$firstSimple->description, //name
                        Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH, //visibility
                        2, //tax_class_id
                        (string)$firstSimple->brand, //ext_brand
                        "kolor:".(string)$firstSimple->color , //ext_color
                        (string)$firstSimple->collection, //ext_productline
                        "Kategoria:".(string)$firstSimple->clothes_description, //ext_category

                        (string)$firstSimple->barcode, //ean

                        "Kolekcja:" . (string)$firstSimple->description2, //col1
                        "gender:" . (string)$firstSimple->gender, //col2
                        "intake:" . (string)$firstSimple->intake, //col3
                        "clothes_type:" . (string)$firstSimple->clothes_type, //col4
                        "size_group:" . (string)$firstSimple->size_group, //col5
                        "week_no:" . (string)$firstSimple->week_no, //col6
                        "barcode:" . (string)$firstSimple->barcode, //col7

                        //magazyn dla konfigurowalnych - zarządzaj stanami = nie
                        0,  //use_config_manage_stock
                        0, //manage_stock
                        0, //qty
                        0, //is_in_stock



                        "", //re_skus

                );
                $configurableSku[] = $vendorId . "-" . $skuConfigurable;
            }

            $this->setSimpleSkus($simpleSku);
            $this->setConfigurableSkus($configurableSku);

            $upload_dir  = Mage::getBaseDir('var').'/import/tmp/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 07777, true);

            $fp = fopen($upload_dir.'wojcikImport.csv', 'w');
            foreach ($listSimples as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);
            $fpConf = fopen($upload_dir.'wojcikImportConf.csv', 'w');
            foreach ($listConfigurable as $fieldsConf) {
                fputcsv($fpConf, $fieldsConf);
            }
            fclose($fpConf);
            exec('php '.Mage::getBaseDir() . DS .'magmi/cli/magmi.cli.php -chain=wojcik:xcreate,wojcik_conf:xcreate');

            $this->updateAdditionalAttributes();

            die("test");
            //--create csv


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
                    'manufacturer' => $vendorId
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
                "size" => (string)$simpleXMLData->size,
                "ean" => $simpleXMLData->barcode,            


                //magazyn dla prostych - zarządzaj stanami tak, ilość 0, dostępność - brak w magazynie
                "manage_stock" => 1,
                "qty" => 0,
                "is_in_stock" => 0
            );
            // Now ingest item into magento
            $dp->ingest($product);
            $this->setSimpleSkus($simpleSku);
        }
        unset($simpleXMLData);


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
        $dp->ingest($productConfigurable);
        $skusUpdated[$configurableSku] = $subskus;
        $this->setConfigurableSkus($configurableSku);

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
