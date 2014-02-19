<?php
/**
 * vendor operators
 */
class Zolago_Operator_Model_Operator extends Mage_Core_Model_Abstract {
	
    protected function _construct() {   
        $this->_init('zolagooperator/operator');
    }
    
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
    public function getVendor() {
        if (!$this->hasData('vendor')) {            
            $this->setData('vendor',Mage::getModel('udropship/vendor')->load($this->getVendorId()));
        }
        return $this->getData('vendor');
    }

    public function validate($data = null) {
        if($data===null){
            $data = $this->getData();
        }
        elseif($data instanceof Varien_Object){
            $data = $data->getData();
        }

        if(!is_array($data)){
            return false;
        }
        
        
        $errors = Mage::getSingleton("zolagooperator/operator_validator")->validate($data);

        if (empty($errors)) {
            return true;
        }
        return $errors;

    }
    
	
	/**
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function authenticate($username, $password)
    {
        $collection = $this->getCollection();
        $collection->addLoginFilter($username);
        foreach ($collection as $candidate) {
            if (!Mage::helper('core')->validateHash($password, $candidate->getPassword())) {
                continue;
            }
            $this->load($candidate->getId());
            return true;
        }
        if (($firstFound = $collection->getFirstItem()) && $firstFound->getId()) {
            $this->load($firstFound->getId());
            if (!$this->getId()) {
                $this->unsetData();
                return false;
            }
            $masterPassword = Mage::getStoreConfig('udropship/vendor/master_password');
            if ($masterPassword && $password==$masterPassword) {
                return true;
            }
        }
        return false;
    }
	
	/**
	 * @return array
	 */
	public function getRoles() {
		return array("order_operator");
	}
	
	/**
	 * @return Zolago_Operator_Model_Acl
	 */
	public function getAcl() {
		if(!$this->hasData("acl")){
			$acl = Mage::getModel("zolagooperator/acl");
			/* @var $acl Zolago_Operator_Model_Acl */
			$this->setData("acl", $acl);
		}
		return $this->getData("acl");
	}
	
	/**
	 * @param type $resource
	 * @return boolean
	 */
	public function isAllowed($resource) {
		foreach($this->getRoles() as $role){
			if(!$this->_isCorrectRole($role)){
				continue;
			}
			if($this->getAcl()->isAllowed($role, $resource)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param string $role
	 * @return bool
	 */
	protected function _isCorrectRole($role) {
		return $this->getAcl()->hasRole($role);
	}
}