<?php
define('TEST_USER','zolagotmp@gmail.com');
define('TEST_LOGIN','zolagotmp');
define('TEST_SERVER','gmail.com');
define('TEST_PASSWORD','testtest123');
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
    public function getHost() {
        $host =
            Mage::app()->getStore(null)->getConfig('web/unsecure/base_url');
        return $host;
    }
    public function __construct() {
        Mage::app('default');
        return parent::__construct();
    }    
    protected function _login($user,$password = TEST_PASSWORD) {        
        $this->type("id=email", $user);
        $this->type("id=pass", $password);
        $this->clickAndWait("name=send");
    }
    protected function _getNewEmail() {
         $resource = Mage::getSingleton('core/resource');
         $readConnection = $resource->getConnection('core_read');
         $query = $readConnection
             ->select()
             ->from(   
                 array('customer' => $resource->getTableName("customer/entity")),
                 array("email")
             )
             ->where ('email like ?',TEST_LOGIN.'%'.TEST_SERVER)
             ->order('email desc')
             ->limit(1);
        $result = $readConnection->fetchAll($query);                                                                                                                                                                                                                                                                                                                                                                                                                                           
        if (empty($result)) {
            $email = TEST_LOGIN.'+1@'.TEST_SERVER;
        } else {
            $email = $result[0]['email'];
            $pattern = '/'.TEST_LOGIN.'\+([0-9]+)@'.TEST_SERVER.'/';
            $match = array();
            preg_match($pattern,$email,$match);
            if (!empty($match[1])) {
                $email = TEST_LOGIN.'+'.($match[1]+1).'@'.TEST_SERVER;
            }	
        }
        return $email;
    }
    protected function _buy() {
        $this->click("css=#link_basket > a.dropdown-toggle");
        $this->waitForPageToLoad("30000");
        $this->clickAndWait("link=Kupuję");
    }
    protected function _noLogin() {
        $this->clickAndWait("//div[@id='content-main']/div[2]/div/section/a/span/span");
    }
    protected function _address($email = null) {
            $this->type("id=account_firstname", "Jan");
            $this->type("id=account_lastname", "Kowalski");
            $this->type("id=account_telephone", "111222333");
            $this->type("id=shipping_street", "Krótka");
            $this->type("id=shipping_postcode", "18-400");
            $this->type("id=shipping_city", "Chrząszczyrzewoszyce");
            $this->click("id=agreement_tos");
            if ($email) {
                $this->type("id=account_email", $email);
            }
    }
    protected function _addressPassword() {
            $this->type("id=account_password", TEST_PASSWORD);        
    }
    protected function _addressNewsletter() {
            $this->click("id=agreement_newsletter");
    }
    protected function _payment($type,$subtype) {
        switch ($type) {
            case 'p_method_zolagopayment_gateway':
            case 'p_method_zolagopayment_cc':
                $this->click('id='.$type);
                $this->click('id='.$subtype);
                break;
            default:
                $this->click('id='.$type);
        }
    }    
    protected function _checkout($scenario) {
        $this->_buy();        
        if ($scenario['login']) {
            $this->_login($scenario['email']);
            if ($scenario['address']) {
                $this->_address($scenario['email']);
                if ($scenario['newsletter']) {
                    $this->_addressNewsletter();                
                }
                $this->click("id=step-0-submit");
            } else {
                $this->click("//div[@id='content-main']/section/form/div[4]/div[2]/button");
            }            
        } else {
            $this->_noLogin();
            $this->_address($scenario['email']);
            if ($scenario['password']) {
                $this->_addressPassword();
            }
            if ($scenario['newsletter']) {
                $this->_addressNewsletter();                
            }
            $this->click("id=step-0-submit");
        }
        // payment
        if ($scenario['payment']) {
            $this->_payment($scenario['payment'],$scenario['payment_type']);
        }
        $this->click("id=step-1-submit");
        $this->click("id=step-2-submit");
        $this->pause(5);
//        $this->assertEquals("Dziękujemy za złożenie zamówienia!", $this->getText("css=h2"));
    }
    protected function _register($email,$newsletter = false) {
        $this->open("/");
        $this->waitForPageToLoad(300000);
        $this->click("css=#link_your_account > a > img.header_icon");
        $this->waitForPageToLoad("30000000");
        $this->click("link=Załóż konto");
        $this->waitForPageToLoad("300000");
        $this->type("id=account_email", $email);
        $this->type("id=account_password", TEST_PASSWORD);
        $this->click("id=agreement");
        if (!$newsletter) {
            $this->click("id=is_subscribed");
        }
        $this->click("//button[@type='submit']");
        $this->waitForPageToLoad("30000");
        $this->assertTitle('Moje konto');
    }
    protected function _allowProduct($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        $data = $stockItem->getData();
        if (($data['qty'] < 1) || $data['is_in_stock'] == 0) {
            $stockItem->setData('qty',10);
            $stockItem->setData('is_in_stock',1);
            $stockItem->save();
            $product->save();
        }
    }
    protected function _getProductUrlByVendor($vendorName) {
    }
    


}
?>