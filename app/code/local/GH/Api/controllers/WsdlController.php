<?php
/**
 * display wsdl
 */
class GH_Api_WsdlController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
        $this->getResponse()	
            ->setHeader('Content-type','text/xml',true);
    }

	public function testAction() {
		/** @var GH_Api_Model_User $user */
		$user = Mage::getModel('ghapi/user');
		try {
			//$user->createUser(3, 'testtest123');



		//$user->loginUser(3,'testtest123','e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855');
		$user->loginBySessionToken('3078f9a845bc3b80b59d4102fdd49b3921da7c1675b4ff2173d59a03af44fc8c');
		} catch(Exception $e) {
			echo $e->getMessage()."\n\n\n";
		}
		echo "Is logged in: ".($user->isLoggedIn() ? 'yes' : 'no');
		var_dump($user->getData());
		var_dump($user->getSession());
	}
}