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

    /**
     * ZolagoOs_Import_Model_Import_Product constructor.
     */
    public function __construct()
    {
        $this->_vendor = $this->getExternalId();
    }


    /**
     * Implement _getImportEntityType() method.
     */
    protected function _getImportEntityType()
    {
       return "product";
    }

    /**
     * File name for _getPath()
     *
     * @return string
     */
    public function _getFileName()
    {
        return $this->getHelper()->getProductFile();

    }
    protected function _import()
    {
        $vendorId = $this->getExternalId();
        $fileName = $this->_getPath();

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
                    $configurableSkuv = explode("/", (string)$productXML->sku)[0];
                    if (
                        $this->startsWith($configurableSkuv, 'WW')
                        || $this->startsWith($configurableSkuv, 'LW')
                        || $this->startsWith($configurableSkuv, 'CW')
                    ) {
//            1. dla indeksów zaczynących się od WW, LW oraz CW np. WW1613SU1G041/068
//            budujemy indeks konfigurowalny odcinając rozmiar /068 i jeden znak przed rozmiarem
                        $configurableSkuv = substr($configurableSkuv, 0, -1);
                    }
                    $skuBatch[$configurableSkuv][] = $productXML;
                    unset($configurableSkuv);
                }
            }

            if (is_object($xmlToArray["item"])) {
                $productXML = $xmlToArray["item"];
                $configurableSkuv = explode("/", (string)$productXML->sku)[0];
                if (
                    $this->startsWith($configurableSkuv, 'WW')
                    || $this->startsWith($configurableSkuv, 'LW')
                    || $this->startsWith($configurableSkuv, 'CW')
                ) {
//            1. dla indeksów zaczynących się od WW, LW oraz CW np. WW1613SU1G041/068
//            budujemy indeks konfigurowalny odcinając rozmiar /068 i jeden znak przed rozmiarem
                    $configurableSkuv = substr($configurableSkuv, 0, -1);
                }
                $skuBatch[$configurableSkuv][] = $productXML;
                unset($configurableSkuv);
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
            $dp->beginImportSession($importProfile, "update", new ZolagoOs_Import_Model_ImportProductsLogger());
            $this->updateRelations($dp,$skusCreated);
            $this->updateVendorAssign($vendorId,$skusCreated);
            $dp->endImportSession();


            //3. Set additional attributes
            $this->updateAdditionalAttributes();


            //4. Move processed file
            $this->_moveProcessedFile();

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    
    /**
     * assign products to vendor
     */
    public function updateVendorAssign($vendorId,$skusCreated) {
        $list = array();
        foreach ($skusCreated as $skuConfigurable=>$simples) {
            foreach ($simples as $item) {
                $list[] = $item;
            }
            $list[] = $skuConfigurable;            
        }
        if (empty($list)) return; //no skus no assign
        $resource = Mage::getSingleton("core/resource");
        $connection = $resource->getConnection('core_write');
        $table = $resource->getTableName('udropship/vendor_product_assoc');
        $productTable = $resource->getTableName('catalog/product');
        $skuList = implode("','",$list);
        $query = "INSERT IGNORE INTO {$table} (vendor_id,product_id,is_attribute,is_udmulti) ".
            "SELECT {$vendorId},entity_id,1,0 from {$productTable} WHERE sku in ('$skuList')";
        $connection->query($query);                
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




    protected function _getFileExtension()
    {
        return "xml";
    }
    /**
     *
     */
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
        $aM->updateAttributesPure($ids, array('udropship_vendor' => $vendorId), 0);

        //3b. Set additional attributes (status opisu = niezatwierdzony for all products)
//        $aM->updateAttributesPure($ids, array('description_status' => 1), 0);

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

    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
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
            $dp->ingest($product);
            $this->setSimpleSkus($simpleSku);
        }


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
            "tax_class_id" => 2,
            "attribute_set" => $attributeSet,
            "store" => "admin",
            "configurable_attributes" => "size",
            "simples_skus" => implode(",", $subskus),

            "description" => $firstSimple->clothes_description,
            "short_description" => $firstSimple->description2,
            "ean"	=> $firstSimple->barcode,

            //ext_
            "ext_productline" => $firstSimple->collection,
            "ext_category" => $firstSimple->clothes_description,
            "ext_color" => $firstSimple->color,
            "ext_brand" => $firstSimple->brand,

            "col1" => "Kolekcja:" . $firstSimple->description2,

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


}
