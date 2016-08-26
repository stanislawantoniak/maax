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

        Mage::getModel("zolagoosimport/observer")->cronImportProducts();
    }


}

$shell = new ZolagoOs_Products_Import();
$shell->run();