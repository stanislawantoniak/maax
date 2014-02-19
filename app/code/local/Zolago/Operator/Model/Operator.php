<?php
/**
 * vendor operators
 */
class Zolago_Operator_Model_Operator extends Mage_Core_Model_Abstract {
	
    protected function _construct() {   
        $this->_init('zolagooperator/operator');
    }
    public function getVendor() {
        if (!$this->hasData('vendor')) {            
            $this->setData('vendor',Mage::getModel('udropship/vendor')->load($this->vendor_id));
        }
        return $this->getData('vendor');
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
            if (!Mage::helper('core')->validateHash($password, $candidate->getPasswordHash())) {
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
}