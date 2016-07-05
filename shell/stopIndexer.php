<?php
/**
 * Modago_ChangeSku_Shell
 */

require_once 'abstract.php';

class Modago_StopIndexer_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_time_limit(36000);

        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

        $query = "UPDATE index_process SET `status` = 'pending' WHERE indexer_code='catalog_product_attribute';";

        /**
         * Execute the query
         */
        $writeConnection->query($query);

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

$shell = new Modago_StopIndexer_Shell();
$shell->run();