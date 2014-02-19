<?php

class Zolago_Dropship_Model_Session extends Unirgy_Dropship_Model_Session
{
	/**
	 * @var Zolago_Operator_Model_Operator
	 */
	protected $_operator;

	/**
	 * @param Zolago_Operator_Model_Operator $operator
	 * @return Zolago_Dropship_Model_Session
	 */
	
	public function setOperator(Zolago_Operator_Model_Operator $operator)
    {
        $this->_operator = $operator;
        return $this;
    }
	
	/**
	 * @return Zolago_Operator_Model_Operator
	 */
	public function getOperator()
    {
        if ($this->_operator instanceof Zolago_Operator_Model_Operator) {
            return $this->_operator;
        }
		$operator = Mage::getModel("zolagooperator/operator");
        if ($this->getOperatorId()) {
            $operator->load($this->getOperatorId());
        }
        $this->setOperator($operator);

        return $this->_vendor;
    }
	
	/**
	 * @return boolean
	 */
    public function isOperatorMode(){
		return (bool)$this->getOperatorMode();
	}
	
	/**
	 * @return boolean
	 */
	public function isVendorMode() {
		return !$this->isOperatorMode();
	}
	
	
	/**
	 * @param Varien_Object $operator
	 * @return Zolago_Dropship_Model_Session
	 */
    public function setOperatorAsLoggedIn($operator)
    {
		// Login in Vendor Context
		$this->setVendorAsLoggedIn($operator->getVendor());
		// Set Operator
        $this->setOperator($operator);
        $this->setOperatorId($operator->getId());
		$this->setOperatorMode(true);
        Mage::dispatchEvent('zolagooperator_operator_login', array('operator'=>$operator));
        return $this;
    }
	
	
	/**
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	public function login($username, $password)
    {
		// Logged as Vendor
		if(parent::login($username, $password)){
			return true;
		}
		$operator = Mage::getModel('zolagooperator/operator');
		/* @var $operator Zolago_Operator_Model_Operator */
		if ($operator->authenticate($username, $password)) {
			$this->setOperatorAsLoggedIn($operator);
			return true;
		}
		return false;
    }
	

	/**
	 * @return bool
	 */
    public function isLoggedIn()
    {
        return parent::isLoggedIn() || 
				($this->isOperatorMode() && $this->getOperatorId() && $this->getOperator()->getId());
    }
}
