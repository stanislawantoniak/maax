<?php
require_once 'abstract.php';

class Modago_Test_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        for ($i = 0; $i < 5; $i++) {
            $priceBatch = $this->generateBatch($i);
            $vi = new Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1();
            $vi::updatePricesConverter($priceBatch);
        }

        $timeStart = microtime(true);
        Zolago_Catalog_Model_Observer::processConfigurableQueue();
        $timeEnd = microtime(true);

        $timeExecution = $timeEnd - $timeStart;
        Mage::log("Execution time (TOTAL): {$timeExecution} seconds", null, "processConfigurableQueue.log") ;

        echo "Execution time (TOTAL): {$timeExecution} seconds";

    }


    public function generateBatch($offset)
    {
        $priceBatch = array();
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->setStore(1);

        $collection->addAttributeToSelect("udropship_vendor");
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $collection->addFieldToFilter('udropship_vendor', array('eq' => 8)); //Levis 5474 simple products

        $select = $collection->getSelect();
        $select->limit(1000, $offset);

        $data = $collection->getData();


        foreach ($data as $_product) {
            $priceA = rand(10, 50);
            $priceMSRP = $priceA + 0.2 * $priceA;
            $priceBatch[$_product["sku"]] = array(
                "A" => $priceA,
                "salePriceBefore" => $priceMSRP
            );
        }

        return $priceBatch;
    }

}

$shell = new Modago_Test_Shell();
$shell->run();

