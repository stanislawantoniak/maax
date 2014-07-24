<?php

abstract class Zolago_Dropship_Block_Vendor_Menu_Abstract extends Mage_Core_Block_Template
{
	const SEPARATOR = "separator";
	
	const ITEM_DASHBOARD = 'dashboard';
	const ITEM_PRODUCTS	  = 'products';
	const ITEM_ORDER	  = 'order';
	const ITEM_HELPDESK  = 'helpdesk';
	const ITEM_RMA		  = 'rma';
	const ITEM_ADVERTISE = 'advertise';
	const ITEM_SETTING	  = 'setting';
	const ITEM_STATEMENTS= 'statements';
	
	const ITEM_DIRECT_ORDER = "direct_order";
	/**
	 *array(
	 *	array(
	 *		"label"		=>	string,
	 *		"url"		=>	null|url,
	 *		"active"	=>	bool,
	 *		"children"	=>	array|null
	 * )
	 * @var array
	 */
	protected static $fullMenu;
	
	
	/**
	 * @return array
	 */
	public function getMenu() {
		return array_intersect_key($this->getFullMenu(), array_flip($this->_sections));
	}
	
	abstract function renderMenu(array $menu);

	/**
	 * @return array
	 */
	public function getFullMenu() {
		if(!self::$fullMenu){
			$sections = array(
				self::ITEM_DASHBOARD	=>	$this->getDashboardSection(),
				self::ITEM_PRODUCTS		=>	$this->getProductSection(),
				self::ITEM_ORDER		=>	$this->getOrderSection(),
				self::ITEM_DIRECT_ORDER =>  $this->getDirectOrderSection(),
				self::ITEM_HELPDESK		=>	$this->getHelpdeskSection(),
				self::ITEM_RMA			=>	$this->getRmaSection(),
				self::ITEM_ADVERTISE	=>	$this->getAdvertiseSection(),
				self::ITEM_SETTING		=>	$this->getSettingSection(),
				self::ITEM_STATEMENTS	=>	$this->getStatementsSection(),
			);
			foreach($sections as $key=>$section){
				if(is_array($section)){
					self::$fullMenu[$key] = $section;
				}
			}
		}
		return self::$fullMenu;
	}
	
	protected function _isUdpoAvailable() {
		if (Mage::helper('udropship')->isUdpoActive()) {
			$session = $this->getSession();
			if($session->isOperatorMode()){
				$operator = $session->getOperator();
				if($operator->isAllowed("udpo/vendor")){
					return true;
				}else{
					return false;
				}
			}
			return true;
		}
		return false;
	}


	public function getDashboardSection() {
		// Dispaly dasboard only order is unavailable
		if(!$this->_isUdpoAvailable()){
			return array(
				"active" => $this->isActive("dashboard"),
				"label"	 => $this->__("Dashboard"),
				"icon"	 => "icon-dashboard",
				"url"	 => $this->getUrl('udropship/vendor/dashboard')
			);
		}
		return null;
	}
	
	public function getDirectOrderSection() {
		if($this->_isUdpoAvailable()){
			return array(
				"active" => $this->isActive("udpo"),
				"icon"	 => "icon-shopping-cart",
				"label"	 => $this->__("Order list"),
				"url"	 => $this->getUrl('udpo/vendor')
			);
		}
		return null;
	}
	
	public function getOrderSection() {
		if($this->_isUdpoAvailable()){
			$group = array(
				array(
					"active" => $this->isActive("udpo"),
					"icon"	 => "icon-tasks",
					"label"	 => $this->__("Order list"),
					"url"	 => $this->getUrl('udpo/vendor')
				),
				array(
					"active" => $this->isActive("zolagopo_aggregated"),
					"icon"	 => "icon-share-alt",
					"label"	 => $this->__("Dispatch lists"),
					"url"	 => $this->getUrl('udpo/vendor_aggregated')
				),
			);
			
		   return array(
				"active" => $this->isActive(array("udpo", "zolagopo_aggregated")),
				"icon"	 => "icon-shopping-cart",
				"label"	 => $this->__("Orders"),
				"url"		=> "#",
				"children" => $this->_processGroups($group)
			);
		}
		return null;
	}
	
	public function getHelpdeskSection() {
		if($this->isModuleActive('udqa') && $this->isAllowed("udqa/vendor")){
			return array(
				"active" => $this->isActive("udqa"),
				"icon"	 => "icon-envelope",
				"label"	 => $this->__('Customer Questions'),
				"url"	 => $this->getUrl('udqa/vendor')
			);
		}
		return null;
	}
	
	public function getRmaSection() {
		if($this->isModuleActive('Unirgy_Rma') && $this->isAllowed("urma/vendor")){
			return array(
				"active" => $this->isActive("urma") || $this->isActive("urmas"),
				"icon"	 => "icon-exclamation-sign",
				"label"	 => $this->__('Returns'),
				"url"	 => $this->getUrl('urma/vendor')
			);
		}
		return null;
	}
	
	public function getAdvertiseSection() {
		$groupOne = array();
		
		if($this->isModuleActive('zolagocampaign') && $this->isAllowed("zolagocampaign/vendor")){
			$groupOne[] = array(
				"active" => $this->isActive("zolagocampaign"),
				"icon"	 => "icon-star",
				"label"	 => $this->__('Campaigns'),
				"url"	 => $this->getUrl('zolagocampaign/vendor/index')
			);
		}
		
		$grouped = $this->_processGroups($groupOne);
		
		if(count($grouped)){
			return array(
				"label"		=> $this->__("Ads. & promotion"),
				"active"	=> $this->isActive(array("zolagocampaign")),
				"icon"		=> "icon-bullhorn",
				"url"		=> "#",
				"children"	=> $grouped
			);
		}
		
		return null;
	}
	
	public function getSettingSection() {
		
		$groupOne = array();
		if($this->isAllowed("udropship/vendor/preferences")){
			$groupOne[] = array(
				"active" => $this->isActive("preferences"),
				"icon"	 => "icon-cog",
				"label"	 => $this->__('Preferences'),
				"url"	 => $this->getUrl('udropship/vendor/preferences')
			);
		}
		
				
		if($this->isModuleActive('zolagooperator') && $this->isAllowed("zolagooperator")){
			$groupOne[] = array(
				"active" => $this->isActive("zolagooperator"),
				"icon"	 => "icon-user",
				"label"	 => $this->__('Agents'),
				"url"	 => $this->getUrl('udropship/operator')
			);
		}
		
		if($this->isModuleActive('zolagopos') && $this->isAllowed("zolagopos")){
			$groupOne[] = array(
				"active" => $this->isActive("zolagopos"),
				"icon"	 => "icon-home",
				"label"	 => $this->__('POS'),
				"url"	 => $this->getUrl('udropship/pos')
			);
		}
		
		if($this->getVendor()->getAllowTiershipModify() && $this->isAllowed("udtiership")){
			$groupOne[] = array(
				"active" => $this->isActive("tiership_rates"),
				"icon"	 => "icon-envelope",
				"label"	 => $this->__('Shipping Rates'),
				"url"	 => $this->getUrl('udtiership/vendor/rates')
			);
		}
		
		$grouped = $this->_processGroups($groupOne);
		
		if(count($grouped)){
			return array(
				"label"		=> $this->__("Settings"),
				"active"	=> $this->isActive(array("preferences", "zolagooperator", "zolagopos", "tiership_rates")),
				"icon"		=> "icon-wrench",
				"url"		=> "#",
				"children"	=> $grouped
			);
		}
		
		return null;
	}
	
	public function getStatementsSection() {
		return null;
	}
	
	/**
	 * @return array
	 */
	public function getProductSection() {
		
		$groupOne = array();
		$groupTwo = array();
		
		// Mass edit
		if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed("udprod/vendor_mass")){
			$groupOne[] = array(
				"active" => $this->isActive("udprod_mass"),
				"label"	 => $this->__('Mass Actions'),
				"icon"	 => "icon-list",
				"url"	 => $this->getUrl('udprod/vendor_mass')
			);
		}
		
		// Mass image
		if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed("udprod/vendor_image")){
			$groupOne[] = array(
				"active"	=> $this->isActive("udprod_image"),
				"label"		=> $this->__('Mass Image'),
				"url"		=> $this->getUrl('udprod/vendor_image'),
				"icon"		=> "icon-picture"
			);
		}

		$grouped = $this->_processGroups($groupOne, $groupTwo);
		
		if(count($grouped)){
			
			return array(
				"label"		=> $this->__("Products"),
				"active"	=> $this->isActive(array("udprod", "udprod_mass", "udprod_image")),
				"icon"		=> "icon-folder-open",
				"url"		=> "#",
				"children"	=> $grouped
			);
		}
		
		return null;
	}
	
	protected function _processGroups() {
		$groups = func_get_args();
		// Just one group do not separate
		if(count($groups)==1){
			return current($groups);
		}
		$out = array();
		foreach($groups as $group){
			if(is_array($group) && count($group)){
				foreach($group as $item){
					$out[] = $item;
				}
				$out[] = self::SEPARATOR;
			}
		}
		// Remove last separator
		array_pop($out);
		return $out;
	}
	
	/**
	 * 
	 * @param type $module
	 * @return type
	 */
	public function isModuleActive($module) {
		return Mage::helper('udropship')->isModuleActive($module) || Mage::helper('core')->isModuleEnabled($module);
	}
	
	/**
	 * @param strign|array $in
	 * @return bool
	 */
	public function isActive($in) {
		if(is_array($in)){
			return in_array($this->getActivePage(), $in);
		}
		return $in==$this->getActivePage();
	}
	
	/**
	 * @param string $resource
	 * @return bool
	 */
	public function isAllowed($resource) {
		return $this->getSession()->isAllowed($resource);
	}
	
	/**
	 * @return null|string
	 */
	public function getActivePage() {
		if ($head = $this->getLayout()->getBlock('header')) {
            return $head->getActivePage();
        }
		return null;
	}
	/**
	 * @return bool
	 */
	public function isOperatorMode() {
		return $this->getSession()->isOperatorMode();
	}
	/**
	 * @return bool
	 */
	public function isLoggedIn() {
		return $this->getSession()->isLoggedIn();
	}
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->getSession()->getVendor();
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('udropship/session');
	}
}