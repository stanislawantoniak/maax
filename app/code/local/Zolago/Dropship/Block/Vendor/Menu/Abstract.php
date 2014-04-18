<?php

class Zolago_Dropship_Block_Vendor_Menu_Abstract extends Mage_Core_Block_Template
{
	const SEPARATOR = "separator";
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
	
	public function renderMenu(array $menu, $withSeparators=false, $useContainer=false, $isSubmenu=false) {
		$str = $useContainer ? "<ul".($isSubmenu ? " class=\"sub-menu\"" : "") . "\">" : "";
		foreach($menu as $item){
			if(is_array($item)){
				$className = array();
				if(isset($item['class'])){
					$className += is_array($item['class']) ? $item['class'] : explode(" ", $item['class']);
				}
				if(isset($item['active']) && $item['active']){
					$className[] = "current";
					$className[] = "open";
				}

				$label = isset($item['label']) ? $this->escapeHtml($item['label']) : "No-Label";

				if(isset($item['url'])){
					$anchor = "<a title=\"%s\" href=\"{$this->escapeHtml($item['url'])}\">%s%s</a>";
				}else{
					$anchor = "<a title=\"%s\">%s%s</a>";
				}

				$icon = "";
				if(isset($item['icon'])){
					$icon = "<i class=\"{$item['icon']}\"></i>";
				}

				$str .= count($className) ?  "<li class=\"".  implode(" ", $className)."\">" : "<li>";
				$str .= sprintf($anchor, $label, $icon, $label);
				if(isset($item["children"])){
					$str.=$this->renderMenu($item["children"], $withSeparators, true, true);
				}
				$str .= "</li>";
			}elseif(is_string($item) && $withSeparators && $item==self::SEPARATOR){
				$str .= "<li class=\"divider\"></li>";
			}
		}
		$str .= $useContainer ? "</ul>" : "";
		return $str;
	}
	
	/**
	 * @return array
	 */
	public function getFullMenu() {
		if(!self::$fullMenu){
			$sections = array(
				$this->getDashboardSection(),
				$this->getProductSection(),
				$this->getOrderSection(),
				$this->getHelpdeskSection(),
				$this->getRmaSection(),
				$this->getAdvertiseSection(),
				$this->getSettingSection(),
				$this->getPaymentsSection()
			);
			foreach($sections as $section){
				if(is_array($section)){
					self::$fullMenu[] = $section;
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
	
	public function getOrderSection() {
		if($this->_isUdpoAvailable()){
			return array(
				"active" => $this->isActive("udpo"),
				"icon"	 => "icon-shopping-cart",
				"label"	 => $this->__("Orders"),
				"url"	 => $this->getUrl('udpo/vendor')
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
				"active" => $this->isActive("urmas"),
				"icon"	 => "icon-exclamation-sign",
				"label"	 => $this->__('Returns'),
				"url"	 => $this->getUrl('udqa/vendor')
			);
		}
		return null;
	}
	
	public function getAdvertiseSection() {
		return null;
	}
	
	public function getSettingSection() {
		
		$groupOne = array();
		if($this->isAllowed("udropship/vendor/preferences")){
			$groupOne[] = array(
				"active" => $this->isActive("preferences"),
				"icon"	 => "icon-wrench",
				"label"	 => $this->__('Preferences'),
				"url"	 => $this->getUrl('udropship/vendor/preferences')
			);
		}
		
				
		if($this->isModuleActive('zolagooperator') && $this->isAllowed("zolagooperator")){
			$groupOne[] = array(
				"active" => $this->isActive("zolagooperator"),
				"icon"	 => "icon-wrench",
				"label"	 => $this->__('Operators'),
				"url"	 => $this->getUrl('udropship/operator')
			);
		}
		
		if($this->isModuleActive('zolagopos') && $this->isAllowed("zolagopos")){
			$groupOne[] = array(
				"active" => $this->isActive("zolagopos"),
				"icon"	 => "icon-wrench",
				"label"	 => $this->__('POS'),
				"url"	 => $this->getUrl('udropship/pos')
			);
		}
		
		if($this->getVendor()->getAllowTiershipModify() && $this->isAllowed("udtiership")){
			$groupOne[] = array(
				"active" => $this->isActive("tiership_rates"),
				"icon"	 => "icon-wrench",
				"label"	 => $this->__('Shipping Rates'),
				"url"	 => $this->getUrl('udtiership/vendor/rates')
			);
		}
		
		$grouped = $this->_processGroups($groupOne);
		
		if(count($grouped)){
			return array(
				"label"		=> $this->__("Settings"),
				"active"	=> $this->isActive(array("preferences", "zolagooperator", "zolagopos", "tiership_rates")),
				"icon"		=> "icon-folder-open",
				"url"		=> "#",
				"children"	=> $grouped
			);
		}
		
		return null;
	}
	
	public function getPaymentsSection() {
		return null;
	}
	
	/**
	 * @return array
	 */
	public function getProductSection() {
		
		$groupOne = array();
		$groupTwo = array();
		
		// Normal edit
		if ($this->isModuleActive('udprod') && $this->isAllowed("udprod/vendor")){
			$groupOne[] = array(
				"active" => $this->isActive("udprod"),
				"label"	 => $this->__('Products Edit'),
				"url"	 => $this->getUrl('udprod/vendor/products')
			);
		}
		
		// Mass edit
		if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed("udprod/vendor_mass")){
			$groupTwo[] = array(
				"active" => $this->isActive("udprod_mass"),
				"label"	 => $this->__('Mass Actions'),
				"url"	 => $this->getUrl('udprod/vendor_mass')
			);
		}
		
		// Mass image
		if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed("udprod/vendor_image")){
			$groupTwo[] = array(
				"active"	=> $this->isActive("udprod_image"),
				"label"		=> $this->__('Mass Image'),
				"url"		=> $this->getUrl('udprod/vendor_image')
			);
		}

		$grouped = $this->_processGroups($groupOne, $groupTwo);
		
		if(count($grouped)){
			return array(
				"label"		=> $this->__("Products"),
				"active"	=> $this->isActive("udprod", "udprod_mass", "udprod_image"),
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