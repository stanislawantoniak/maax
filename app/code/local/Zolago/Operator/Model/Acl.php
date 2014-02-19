<?php

class Zolago_Operator_Model_Acl extends Zend_Acl
{
	// Reousrce definition
	const RES_ORDER = "order";
	
	// Roles definiton
	const ROLE_ORDER_OPERATOR = "order_operator";
	
	// Resources as array
	protected static $_currentResources = array(
		self::RES_ORDER => "Order",	
	);
	
	// Roles as array
	protected static $_currentRoles = array(
		self::ROLE_ORDER_OPERATOR => "Order operator",	
	);
	
	
	public function __construct() {
		// Set resources
		foreach(array_keys(self::$_currentResources) as $resourceCode){
			$this->addResource($resourceCode);
		}
		// Set roles
		foreach(array_keys(self::$_currentRoles) as $roleCode){
			$this->addRole($roleCode);
		}
		
		// Build ACL Rules
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_ORDER_OPERATOR, self::RES_ORDER);
	}
	
	/**
	 * @return array
	 */
	public static function getAllRoles() {
		return self::$_currentRoles;
	}
}
