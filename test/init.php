<?php


/**
 * Compilation includes configuration file
 */

define('MAGENTO_ROOT', getenv('MAGENTO_ROOT')? getenv('MAGENTO_ROOT'):getcwd().'/..');

$mageFilename = MAGENTO_ROOT . '/app/Mage.php';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename." was not found";
    }
    exit;
}


require_once $mageFilename;

// parent zolago test class
class Zolago_TestCase extends PHPUnit_Framework_TestCase {
    public function __construct() {
        Mage::app('default');
        return parent::__construct();
    }
}
?>