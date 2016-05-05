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

        $fileName = $this->getArg("file");
        $updateFilename = Mage::getBaseDir() . '/var/'.$fileName;

        echo "Reading file {$updateFilename} \n";

        $update = array();
        $row = 1;
        if (($f = fopen($updateFilename, "r")) !== FALSE) {
            while (($data = fgetcsv($f, 10000, ",")) !== FALSE) {
                $row++;
                $update[$data[0]] = $data[1];
            }
            fclose($f);
        } else {
            echo "File {$fileName} does not exist or invalid \n";
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

        echo "Done\n";
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

$shell = new Modago_ChangeSku_Shell();
$shell->run();