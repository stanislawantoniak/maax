<?php
/**
 * Modago_ImportProducts_Shell
 */

require_once 'abstract.php';

require_once("/var/www/orba-zolago-internal/magmi/inc/magmi_defs.php");
//Datapump include
require_once("/var/www/orba-zolago-internal/magmi/integration/inc/magmi_datapump.php");

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
        $xml = simplexml_load_string($fileContent) or die("Error: Cannot create object");
        //print_r((array)$xml);

        $xmlToArray = (array)$xml;


        $vendorId = 87;

        //Collect sku
        $skuBatch = array();
        foreach ($xmlToArray["item"] as $productXML) {
            $skuBatch[explode("/", (string)$productXML->sku)[0]][] = $productXML;
        }
        print_r($skuBatch);

        // create a Product import Datapump using Magmi_DatapumpFactory
        $dp = Magmi_DataPumpFactory::getDataPumpInstance("productimport");


        // Begin import session with a profile & running mode, here profile is "default" & running mode is "create".
        // Available modes:
        // "create" creates and updates items,
        // "update" updates only,
        // "xcreate creates only.
        // Important: for values other than "default" profile has to be an existing magmi profile
        $dp->beginImportSession("local", "create", new ImportProductsLogger());

        foreach ($skuBatch as $configurableSku => $simples) {
            $subskus = array();
            foreach($simples as $simpleXMLData){
                $simpleSkuV = (string)$simpleXMLData->sku;
                $simpleSku = $vendorId . "-" . $simpleSkuV;
                $subskus[] = $simpleSku;  //Collect simple skus for configurable
                $product = array(
                    "name" => $simpleSkuV,
                    "sku" => $simpleSku,
                    "skuv" => $simpleSkuV,
                    //"udropship_vendor" => $vendorId,
                    "price" => "0.00",
                    "type" => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
                    "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE,
                    "tax_class_id" => 0,
                    "attribute_set" => "Ubrania - Dzieci - Spodnie i legginsy",
                    "store" => "admin",
                    "description" => $simpleXMLData->description,
                    "short_description" => $simpleXMLData->description2,
                    "ext_color" => $simpleXMLData->color,
                    "size" => $simpleXMLData->size,
                    //"description_status" => 1,

                    //ext_
                    "ext_productline" => $simpleXMLData->collection

                );
                // Now ingest item into magento
                $dp->ingest($product);
            }

            //Create configurable
Mage::log(implode(",", $subskus));
            $firstSimple = $simples[0];
            $productConfigurable = array(
                "name" => $configurableSku,
                "sku" => $vendorId . "-" . $configurableSku,
                "skuv" => $simpleSku,
                //"udropship_vendor" => $vendorId,
                "price" => "0.00",
                "type" => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                "status" => Mage_Catalog_Model_Product_Status::STATUS_DISABLED,
                "visibility" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                "tax_class_id" => 0,
                "attribute_set" => "Ubrania - Dzieci - Spodnie i legginsy",
                "store" => "admin",
                "configurable_attributes" => "size",
                "description" => $firstSimple->description,
                "short_description" => $firstSimple->description2,
                "description_status" => 1,

                "simples_skus" => implode(",", $subskus)

            );
            // Now ingest item into magento
            $dp->ingest($productConfigurable);

        }


        /* end import session, will run post import plugins */
        $dp->endImportSession();
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