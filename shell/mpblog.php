<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
require_once 'abstract.php';

/**
 * Magento Compiler Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magpleasure_Shell_Mpblog_Import extends Mage_Shell_Abstract
{

    /**
     * Importer
     *
     * @return Magpleasure_Blog_Helper_Import
     */
    public function _importer()
    {
        return Mage::helper('mpblog/import');
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('awblog')) {
            echo "\n************************** \n";
            echo "* Importing AW_Blog data *\n";
            echo "************************** \n";
            try {
                $this->_importer()->importAwblog(true);
                echo "\nReady.\n\n";
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }

        } elseif ($this->getArg('wp')) {

            echo "\n**************************** \n";
            echo "* Importing WordPress data *\n";
            echo "**************************** \n";

            $file = $this->getArg('file');

            if (!Mage::app()->isSingleStoreMode()){

                $stores = $this->getArg('stores');

                $storeIds = array();
                $storeCodes = explode(',', $stores);

                foreach ($storeCodes as $storeName){
                    $store = Mage::getModel('core/store')->load($storeName, 'code');

                    if ($store && $store->getId()){
                        $storeIds[] = $store->getId();
                    }
                }

            } else {
                $storeIds = array(Mage::app()->getDefaultStoreView()->getId());
            }

            if ($file && $storeIds && is_array($storeIds)){

                $data = array(
                    'file' => $file,
                    'stores' => $storeIds,
                );

                try {
                    $this->_importer()->importWordpress(true, $data);
                    echo "\nReady.\n\n";
                } catch (Exception $e) {
                    echo "Error: " . $e->getMessage() . "\n";
                }
            }

        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     * @return string
     */
    public function usageHelp()
    {


        return <<<USAGE
Usage:  php -f import.php [options]
  awblog                        Import data from AW_Blog
  wp                            Import data from Wordpress Blog (-file <PATH_TO_FILE> -stores <STORE_CODE_1,STORE_CODE_2>)
  help                          This help

USAGE;
    }
}

$shell = new Magpleasure_Shell_Mpblog_Import();
$shell->run();
