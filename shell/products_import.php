<?php
/**
 * ZolagoOs_Products_Import
 */

require_once 'abstract.php';

class ZolagoOs_Products_Import extends Mage_Shell_Abstract
{
    public function run()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        ZolagoOs_Import_Model_Observer::cronImportProducts();
    }


}

$shell = new ZolagoOs_Products_Import();
$shell->run();