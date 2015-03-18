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
			$user->createUser(3, 'testtest123');
		} catch(Exception $e) {
			echo $e->getMessage()."\n\n\n";
		}


		//$user->loginUser(3,'testtest123','e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855');
		$user->loginBySessionToken('0bef8a1f31a2f91265163a53b4788977dafd3cf7e3144ade3b1704cbf804312a');
		echo "Is logged in: ".($user->isLoggedIn() ? 'yes' : 'no');
		var_dump($user->getData());
		var_dump($user->getSession());
	}
}