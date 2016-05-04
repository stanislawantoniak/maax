<?php
/**
 * Modago_ChangeSku_Shell
 */

require_once 'abstract.php';

class Modago_ChangeSku_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(36000);

        $updateFilename = Mage::getBaseDir() . '/var/new-skus-luna-83.csv';


        $update = array();
        $row = 1;
        if (($f = fopen($updateFilename, "r")) !== FALSE) {
            while (($data = fgetcsv($f, 1000, ",")) !== FALSE) {
                $row++;
                $update[$data[0]] = $data[1];
            }
            fclose($f);
        }

        if(!empty($update)){
            /**
             * Get the resource model
             */
            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');



            $query = "";
            foreach($update as $newSku=>$oldSku){
                $query .= "UPDATE catalog_product_entity SET sku = '{$newSku}' WHERE sku ='{$oldSku}';";
            }

            /**
             * Execute the query
             */
            $writeConnection->query($query);
        }


        echo "{$updateFilename}\n";
        echo "Done\n";
    }


}

$shell = new Modago_ChangeSku_Shell();
$shell->run();