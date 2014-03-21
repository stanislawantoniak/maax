<?php

class Zolago_Operator_Model_Acl extends Zend_Acl
{
		
	// Roles definiton
	const ROLE_ORDER_OPERATOR						= "order_operator";
	const ROLE_MARKETING_OFFICER					= "marketing_officer";
	const ROLE_RMA_OPERATOR		   			    = "rma_operator";
	const ROLE_HELPDESK							= "helpdesk";
	
	
	// Reousrce definition
	
	// Vendro controller for all
	const RES_UDROPSHIP_VENDOR_SET_LOCALE			= "udropship/vendor/setlocale";
	const RES_UDROPSHIP_VENDOR_WYSIWYG				= "udropship/vendor/wysiwyg";
	const RES_UDROPSHIP_VENDOR_DASHBOARD			= "udropship/vendor/dashboard";
	const RES_UDROPSHIP_VENDOR_INDEX				= "udropship/vendor/index";
	const RES_UDROPSHIP_VENDOR_LOGIN				= "udropship/vendor/login";
	const RES_UDROPSHIP_VENDOR_LOGOUT				= "udropship/vendor/logout";
	const RES_UDROPSHIP_VENDOR_PASSWORD			= "udropship/vendor/password";
	const RES_UDROPSHIP_VENDOR_PASSWORD_POST		= "udropship/vendor/passwordPost"
			;
	// Restricted 
	const RES_UDROPSHIP_VENDOR_PREFERENCES			= "udropship/vendor/preferences";
	const RES_UDROPSHIP_VENDOR_PREFERENCES_POST	= "udropship/vendor/preferencesPost";
	
	// Po Vendor controller - whole
	const RES_UDPO_VENDOR							= "udpo/vendor";
	// Po Vendor controller - whole
	const RES_URMA_VENDOR							= "urma/vendor";
	// Po Vendor controller - whole
	const RES_ASK_QUESTION							= "udqa/vendor";

	// Resources as array
	protected static $_currentResources = array(
		self::RES_UDROPSHIP_VENDOR_SET_LOCALE		=> "Vendor Set locale",	
		self::RES_UDROPSHIP_VENDOR_WYSIWYG			=> "Vendor wysiwyg",	
		self::RES_UDROPSHIP_VENDOR_DASHBOARD		=> "Vendor dashboard",	
		self::RES_UDROPSHIP_VENDOR_INDEX			=> "Vendor index",	
		self::RES_UDROPSHIP_VENDOR_LOGIN			=> "Vendor login",	
		self::RES_UDROPSHIP_VENDOR_LOGOUT			=> "Vendor logout",
		self::RES_UDROPSHIP_VENDOR_PASSWORD		=> "Vendor pasword",
		self::RES_UDROPSHIP_VENDOR_PASSWORD_POST	=> "Vendor password post",
		self::RES_UDROPSHIP_VENDOR_PREFERENCES		=> "Vendor preferneces",
		self::RES_UDROPSHIP_VENDOR_PREFERENCES_POST=> "Vendor preferneces post",
        // PO
		self::RES_UDPO_VENDOR						=> "Orders",
		// RMA
		self::RES_URMA_VENDOR						=> "RMA",
		//  Ask Question
		self::RES_ASK_QUESTION						=> "Vendor ask question"
			
			
	);
	
	// Roles as array
	protected static $_currentRoles = array(
		self::ROLE_ORDER_OPERATOR					=> "Order operator",	
		self::ROLE_MARKETING_OFFICER				=> "Marketing officer",	
		self::ROLE_RMA_OPERATOR					=> "RMA Operator",	
		self::ROLE_HELPDESK						=> "Helpdesk",	
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
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_SET_LOCALE);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_WYSIWYG);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_DASHBOARD);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_INDEX);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_LOGIN);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_LOGOUT);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_PASSWORD);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_PASSWORD_POST);
		
		// Build ACL Rules - Order operator
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_ORDER_OPERATOR, self::RES_UDPO_VENDOR);
		
		// Build ACL Rules - Marketing officer
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_UDROPSHIP_VENDOR_PREFERENCES);
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_UDROPSHIP_VENDOR_PREFERENCES_POST);
		
		// Build ACL Rules - RMA Operator
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_RMA_OPERATOR, self::RES_URMA_VENDOR);
		
		// Build ACL Rules - Helpdesk
		$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_RMA_OPERATOR, self::RES_ASK_QUESTION);
		
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
