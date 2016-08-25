<?php
/**
 * Modago_ImportProducts_Shell
 */

require_once 'abstract.php';

require_once("../magmi/inc/magmi_defs.php");
//Datapump include
require_once("../magmi/integration/inc/magmi_datapump.php");

/**
 * Define a logger class that will receive all magmi logs *
 */
class ImportProductsLogger
{

    /**
     * logging methods
     *
     * @param string $data
     *            : log content
     * @param string $type
     *            : log type
     */
    public function log($data, $type)
    {
        echo "$type:$data\n";
    }
}

class Modago_ImportProducts_Shell extends Mage_Shell_Abstract
{

    public function run()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(36000);

        $fileName = $this->getArg("file");
        echo "Reading file {$fileName} \n";

        $fileContent = file_get_contents($fileName);
        $xml = simplexml_load_string($fileContent) or die("Error: Cannot Read Import File \n");
        //print_r((array)$xml);

        /*Config values*/
        $vendorId = 82;
        $importProfile = "dev_01";

        /*Config values*/

        $xmlToArray = (array)$xml;
        if(empty($xmlToArray))
            die("Error: No Data For Import \n");

        //Collect sku
        $skuBatch = array();
        foreach ($xmlToArray["item"] as $productXML) {
            $skuBatch[explode("/", (string)$productXML->sku)[0]][] = $productXML;
        }
        if(empty($skuBatch))
            die("Error: No Data For Import \n");

        // create a Product import Datapump using Magmi_DatapumpFactory
        $dp = Magmi_DataPumpFactory::getDataPumpInstance("productimport");


        // Begin import session with a profile & running mode, here profile is "default" & running mode is "create".
        // Available modes:
        // "create" creates and updates items,
        // "update" updates only,
        // "xcreate creates only.
        // Important: for values other than "default" profile has to be an existing magmi profile
        $dp->beginImportSession($importProfile, "create", new ImportProductsLogger());

        $skusUpdated = array();
        foreach ($skuBatch as $configurableSkuv => $simples) {
            $u = $this->insertConfigurable($dp, $vendorId, $configurableSkuv, $simples);
            $skusUpdated = array_merge($skusUpdated,$u);
        }

        /* end import session, will run post import plugins */
        $dp->endImportSession();


        //Update udropship_vendor
        // MAGMI: warning:Potential assignment problem, specific model found for select attribute => udropship_vendor(udropship/vendor_source)

        /* @var $collectionS Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addFieldToFilter("sku", array("in" => $skusUpdated));
        $ids = $collection->getAllIds();

        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');
        $aM->updateAttributesPure($ids,array('udropship_vendor' => $vendorId),0);
    }


    /**
     * @param $dp
     * @param $vendorId
     * @param $configurableSkuv
     * @param $simples
     * @return array
     */
    public function insertConfigurable($dp, $vendorId, $configurableSkuv, $simples) {
        $attributeSet = "Do przeniesienia";

        $skusUpdated = [];
        $subskus = array();
        foreach($simples as $simpleXMLData) {
            $simpleSkuV = (string)$simpleXMLData->sku;
            $simpleSku = $vendorId . "-" . $simpleSkuV;
            $subskus[] = $simpleSku;  //Collect simple skus for configurable
            $product = array(
                           "name" => $simpleSkuV,
                           "sku" => $simpleSku,
                           "skuv" => $simpleSkuV,
                           //"price" => "0.00",
                           "type" => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                           "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
                           "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
                           "tax_class_id" => 0,
                           "attribute_set" => $attributeSet,
                           "store" => "admin",
                           "description" => $simpleXMLData->description,
                           "short_description" => $simpleXMLData->description2,
                           "size" => $simpleXMLData->size,
                           "ean" => $simpleXMLData->barcode,
                           //"description_status" => 1,

                           //ext_
                           "ext_productline" => $simpleXMLData->collection,
                           "ext_category" => $simpleXMLData->clothes_description,
                           "ext_color" => $simpleXMLData->color,
                           "ext_brand" => $simpleXMLData->brand,

                           //col
                           "col1" => $simpleXMLData->unit_of_measure,
                           "col2" => $simpleXMLData->gender,
                           "col3" => $simpleXMLData->intake,
                           "col4" => $simpleXMLData->clothes_type,
                           "col5" => $simpleXMLData->size_group,
                           "col6" => $simpleXMLData->week_no,
                           "col7" => $simpleXMLData->barcode

                       );
            // Now ingest item into magento
            $dp->ingest($product);
            $skusUpdated[] = $simpleSku;
        }

        //Create configurable
        $firstSimple = $simples[0];
        $configurableSku = $vendorId . "-" . $configurableSkuv;
        $productConfigurable = array(
                                   "name" => $configurableSkuv,
                                   "sku" => $configurableSku,
                                   "skuv" => $simpleSku,
                                   //"price" => "0.00",
                                   "type" => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                                   "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
                                   "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                                   "tax_class_id" => 0,
                                   "attribute_set" => $attributeSet,
                                   "store" => "admin",
                                   "configurable_attributes" => "size",
                                   "simples_skus" => implode(",", $subskus),
                                   "ean" => $firstSimple->barcode,

                                   "description" => $firstSimple->description,
                                   "short_description" => $firstSimple->description2,
                                   //"description_status" => 1,

                                   //ext_
                                   "ext_productline" => $firstSimple->collection,
                                   "ext_color" => $firstSimple->color,
                                   "ext_category" => $firstSimple->clothes_description,
                                   "ext_brand" => $firstSimple->brand,

                                   //col
                                   "col1" => $firstSimple->unit_of_measure,
                                   "col2" => $firstSimple->gender,
                                   "col3" => $firstSimple->intake,
                                   "col4" => $firstSimple->clothes_type,
                                   "col5" => $firstSimple->size_group,
                                   "col6" => $firstSimple->week_no,
                                   "col7" => $firstSimple->barcode
                               );
        // Now ingest item into magento
        $dp->ingest($productConfigurable);
        $skusUpdated[] = $configurableSku;

        return $skusUpdated;
    }


    /**
     * Retrieve argument value by name or false
     *
     * @param string $name the argument name
     * @return mixed
     */
    public function getArg($name)
    {
        if (isset($this->_args[$name])) {
            return $this->_args[$name];
        }
        return false;
    }

}

$shell = new Modago_ImportProducts_Shell();
$shell->run();