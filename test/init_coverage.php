<?php


/**
 * Compilation includes configuration file
 */

!defined('MAGENTO_ROOT') 
    && define('MAGENTO_ROOT', getenv('MAGENTO_ROOT')? getenv('MAGENTO_ROOT'):getcwd().'/..');

$mageFilename = MAGENTO_ROOT . '/app/Mage.php';

if (!file_exists($mageFilename)) {
    if (is_dir('downloader')) {
        header("Location: downloader");
    } else {
        echo $mageFilename." was not found";
    }
    exit;
}
function no_coverage() {
    return false;
}

require_once $mageFilename;

// parent zolago test class
class Zolago_TestCase extends PHPUnit_Framework_TestCase {
    protected $_testData;
    /**
     * validator test     
     */
    protected function _validateTest($testKey,$testField,$expected) {
        $model = $this->_getModel();
        $testData = $this->_testData; //Zolago_Pos_Helper_Test::getPosData();
        $testData[$testKey] = $testField;
        $model->setData($testData);
        $validator = $model->validate();
        $this->assertContains($expected,$validator);
    }

    public function __construct() {
        Mage::app('default');
        return parent::__construct();
    }
    // 
}
?>