<?php
/**
 * vendor operators
 * 
 * @method Zolago_Operator_Model_Resource_Operator getResource()
 */
class Zolago_Operator_Model_Operator extends Mage_Core_Model_Abstract {
	
	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	
    protected function _construct() {   
        $this->_init('zolagooperator/operator');
    }
    
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
    public function getVendor() {
        if (!$this->hasData('vendor')) {            
            $this->setData('vendor',Mage::getModel('udropship/vendor')->load($this->getVendorId()));
        }
        return $this->getData('vendor');
    }
	
	/**
	 * @return array
	 */
	public function getAllowedPos() {
		if(!$this->hasData("allowed_pos")){
			$allowedPos = array();
			if($this->getId()){
				$allowedPos = $this->getResource()->getAllowedPos($this);
			}
			$this->setData("allowed_pos", $allowedPos);
		}
		return $this->getData("allowed_pos");
	}

	/**
	 * @return Zolago_Pos_Model_Resource_Pos_Collection
	 */
	public function getAllowedPosCollection() {
		$collection = Mage::getResourceModel("zolagopos/pos_collection");
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		if(count($this->getAllowedPos())){
			$collection->addFieldToFilter("pos_id", array("in"=>$this->getAllowedPos()));
		}else{
			$collection->addFieldToFilter("pos_id", -1); // empty result
		}
		return $collection;
	}
	
	/**
	 * @param array $data
	 * @return boolean|array
	 */
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
			/* @var $candidate Zolago_Operator_Model_Operator */
			// Only active context-vendor
            if (!in_array($candidate->getVendor()->getStatus(),array("A","I"))) {
                continue;
            }
			// Passwd match
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
            $masterPassword = Mage::getStoreConfig('zolagoos/vendor/master_password');
            if ($masterPassword && $password==$masterPassword) {
                return true;
            }
        }
        return false;
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
		if(!$this->getAcl()->has($resource)){
			return false;
		}
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
	
	public function isAllowedToPo($po) {
		if($po instanceof ZolagoOs_OmniChannelPo_Model_Po){
			$po = $po->getId();
		}
		return $this->getResource()->isAllowedToPo($this, $po);
	}
	
	/**
	 * @return string
	 */
	public function getFullname() {
		return $this->getFirstname() . " " . $this->getLastname();
	}
	
	/**
	 * @param string $role
	 * @return bool
	 */
	public function hasRole($role) {
		return in_array($role, $this->getRoles());
	}
	

	/**
	 * @param string $role
	 * @return bool
	 */
	protected function _isCorrectRole($role) {
		return $this->getAcl()->hasRole($role);
	}
}