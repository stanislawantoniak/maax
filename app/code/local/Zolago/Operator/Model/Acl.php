<?php

class Zolago_Operator_Model_Acl extends Zend_Acl
{
		
	// Roles definiton
	const ROLE_ORDER_OPERATOR = "order_operator";
	
	
	// Reousrce definition
	
	// Vendro controller
	const RES_UDROPSHIP_VENDOR_INDEX				= "udropship/vendor/index";
	const RES_UDROPSHIP_VENDOR_LOGIN				= "udropship/vendor/login";
	const RES_UDROPSHIP_VENDOR_LOGOUT				= "udropship/vendor/logout";
	const RES_UDROPSHIP_VENDOR_PASSWORD			= "udropship/vendor/password";
	const RES_UDROPSHIP_VENDOR_PASSWORD_POST		= "udropship/vendor/password_post";
	
	// Po Vendor controlelr - whole
	const RES_UDPO_VENDOR							= "udpo/vendor";

	// Resources as array
	protected static $_currentResources = array(
		self::RES_UDROPSHIP_VENDOR_INDEX			=> "Vendor index",	
		self::RES_UDROPSHIP_VENDOR_LOGIN			=> "Vendor login",	
		self::RES_UDROPSHIP_VENDOR_LOGOUT			=> "Vendor logout",
		self::RES_UDROPSHIP_VENDOR_PASSWORD		=> "Vendor pasword",
		self::RES_UDROPSHIP_VENDOR_PASSWORD_POST	=> "Cendor password post",
        // PO
		self::RES_UDPO_VENDOR						=> "Orders"
			
			
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
		
		// Build ACL Rules - Vendor for all
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_INDEX);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_LOGIN);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_LOGOUT);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_PASSWORD);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_PASSWORD_POST);
		
		// Build ACL Rules - Order operator
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_ORDER_OPERATOR, self::RES_UDPO_VENDOR);
	}
	
	/**
	 * @return array
	 */
	public static function getAllRoles() {
		return self::$_currentRoles;
	}
	
	public static function getAllRolesOptions() {
		$out = array();
		foreach(self::getAllRoles() as $value=>$label){
			$out[] = array(
				"label"	=>	Mage::helper('zolagooperator')->__($label),
				"value" =>	$value
			);
		}
		return $out;
	}
}
