<?php


/**
 * Compilation includes configuration file
 */

!defined('MAGENTO_ROOT') 
    && define('MAGENTO_ROOT', getenv('MAGENTO_ROOT')? getenv('MAGENTO_ROOT'):getcwd().'/..');

$mageFilename = MAGENTO_ROOT . '/app/Mage.php';

function no_coverage() {
    return true;
}

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

class ZolagoDb_TestCase extends Zolago_TestCase{
	/**
	 * @var Varien_Db_Adapter_Interface
	 */
	protected $_conn;

	public function __construct() {
		parent::__construct();
		$this->_conn = Mage::getSingleton('core/resource')->
				getConnection('core_write');
		$this->_conn->beginTransaction();
	}
	
	public function __destruct() {
		$this->_conn->rollBack();
	}
	
}
class ZolagoSelenium_TestCase extends PHPUnit_Extensions_SeleniumTestCase {
    public function __construct() {
        Mage::app('default');
        return parent::__construct();
    }    
}
?>