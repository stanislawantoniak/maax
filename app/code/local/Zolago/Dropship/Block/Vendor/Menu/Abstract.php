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
	const ITEM_LOYALTY_CARD = 'loyalty_card';
    const ITEM_SETTING	  = 'setting';
    const ITEM_REGULATIONS = "regulation";
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
        if(!self::$fullMenu) {
            $sections = array(
                            self::ITEM_DASHBOARD	=>	$this->getDashboardSection(),
                            self::ITEM_PRODUCTS		=>	$this->getProductSection(),
                            self::ITEM_ORDER		=>	$this->getOrderSection(),
                            self::ITEM_DIRECT_ORDER =>  $this->getDirectOrderSection(),
                            self::ITEM_HELPDESK		=>	$this->getHelpdeskSection(),
                            self::ITEM_RMA			=>	$this->getRmaSection(),
                            self::ITEM_ADVERTISE	=>	$this->getAdvertiseSection(),
                            self::ITEM_LOYALTY_CARD	=>	$this->getLoyaltyCardSection(),
                            self::ITEM_SETTING		=>	$this->getSettingSection(),
                            self::ITEM_REGULATIONS	=>	$this->getRegulationsSection(),
                            self::ITEM_STATEMENTS	=>	$this->getStatementsSection(),
                        );
            foreach($sections as $key=>$section) {
                if(is_array($section)) {
                    self::$fullMenu[$key] = $section;
                }
            }
        }
        return self::$fullMenu;
    }

    protected function _isUdpoAvailable() {
        if (Mage::helper('udropship')->isUdpoActive()) {
            $session = $this->getSession();
            if($session->isOperatorMode()) {
                $operator = $session->getOperator();
                if($operator->isAllowed("udpo/vendor")) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        }
        return false;
    }


    public function getDashboardSection() {
        // Dispaly dasboard only order is unavailable
        if(!$this->_isUdpoAvailable()) {
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
        if($this->_isUdpoAvailable()) {
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
        if($this->_isUdpoAvailable()) {
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
        if($this->isModuleActive('udqa') && $this->isAllowed("udqa/vendor")) {
            return array(
                       "active" => $this->isActive("udqa"),
                       "icon"	 => "icon-envelope",
                       "label"	 => $this->__('Customer Questions'),
                       "url"	 => $this->getUrl('udqa/vendor')
                   );
        }
        return null;
    }

	/**
	 * Regulation section (visible only for vendors in modago)
	 *
	 * @return array|null
	 */
	public function getRegulationsSection() {
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zolagocommon");
		if ($this->isModuleActive('ghregulation') && 
			$this->isAllowed(Zolago_Operator_Model_Acl::RES_VENDOR_RULES) &&
			$commonHlp->useGalleryConfiguration()
		) {
			return array(
				"label" => $this->__("Terms of cooperation"),
				"active" => $this->isActive(array("ghregulation")),
				"icon" => "icon-flag",
				"url" => $this->getUrl('udropship/vendor/rules'),
			);
		}
		return null;
	}

    public function getRmaSection() {
        if($this->isModuleActive('ZolagoOs_Rma') && $this->isAllowed("urma/vendor") && !$this->isModuleActive('ZolagoOs_NoRma')) {
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

        if($this->isModuleActive('zolagocampaign')) {
            if ($this->isAllowed(Zolago_Operator_Model_Acl::RES_CAMPAIGN_VENDOR)) {
                $groupOne[] = array(
                                  "active" => $this->isActive("zolagocampaign"),
                                  "icon"   => "icon-star",
                                  "label"  => $this->__('Campaigns'),
                                  "url"    => $this->getUrl('zolagocampaign/vendor/index')
                              );
            }
            if ($this->isAllowed(Zolago_Operator_Model_Acl::RES_CAMPAIGN_PLACEMENT)) {
                $groupOne[] = array(
                                  "active" => $this->isActive("zolagocampaign_placement"),
                                  "icon"   => "icon-th",
                                  "label"  => $this->__('Manage placements'),
                                  "url"    => $this->getUrl('zolagocampaign/placement/index')
                              );
            }
        }
        if($this->isModuleActive('ghmarketing') && $this->getVendor()->getData('marketing_charges_enabled')) {
            if ($this->isAllowed(Zolago_Operator_Model_Acl::RES_BUDGET_MARKETING)) {
                $groupOne[] = array(
                                  "active" => $this->isActive("budget_marketing"),
                                  "icon"   => "icon-usd",
                                  "label"  => Mage::helper('ghmarketing')->__('Budget and marketing costs'),// Todo translate
                                  "url"    => $this->getUrl('udropship/marketing/budget')
                              );
            }
        }
        $grouped = $this->_processGroups($groupOne);

        if(count($grouped)) {
            return array(
                       "label"		=> $this->__("Ads. & promotion"),
                       "active"     => $this->isActive(array("zolagocampaign", "zolagocampaign_placement", "budget_marketing")),
                       "icon"		=> "icon-bullhorn",
                       "url"		=> "#",
                       "children"	=> $grouped
                   );
        }

        return null;
    }

	public function getLoyaltyCardSection() {
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zolagocommon");
		if ($this->isModuleActive('ZolagoOs_LoyaltyCard')
			&& $this->isAllowed(Zolago_Operator_Model_Acl::RES_LOYALTY_CARD)
			&& $commonHlp->useLoyaltyCardSection()) {
			return array(
				"active" => $this->isActive("zos-loyalty-card"),
				"icon"	 => "icon-user",
				"label"	 => $this->__('Loyalty cards'),
				"url"	 => $this->getUrl('loyalty/card')
			);
			/** @see ZolagoOs_LoyaltyCard_CardController::indexAction() */
		}
		return null;
	}

    public function getSettingSection() {

        $groupOne = array();

        if(!$this->isOperatorMode() || $this->isAllowed(Zolago_Operator_Model_Acl::RES_VENDOR_SETTINGS)) {
            $groupOne[] = array(
                              "active" => $this->isActive("vendorsettings_info"),
                              "icon"	 => "icon-briefcase",
                              "label"	 => $this->__('Company settings'),
                              "url"	 => $this->getUrl('udropship/vendor_settings/info')
                          );
            $groupOne[] = array(
                              "active" => $this->isActive("vendorsettings_shipping"),
                              "icon"	 => "icon-plane",
                              "label"	 => $this->__('Shipment settings'),
                              "url"	 => $this->getUrl('udropship/vendor_settings/shipping')
                          );
            if(!$this->isModuleActive('ZolagoOs_NoRma')){
                $groupOne[] = array(
                    "active" => $this->isActive("vendorsettings_rma"),
                    "icon"	 => "icon-retweet",
                    "label"	 => $this->__('RMA settings'),
                    "url"	 => $this->getUrl('udropship/vendor_settings/rma')
                );
            }
        }





        if($this->isModuleActive('zolagooperator') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_UDROPSHIP_OPERATOR)) {
            $groupOne[] = array(
                              "active" => $this->isActive("zolagooperator"),
                              "icon"	 => "icon-user",
                              "label"	 => $this->__('Agents'),
                              "url"	 => $this->getUrl('udropship/operator')
                          );
        }

        if($this->isModuleActive('zolagopos') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_UDROPSHIP_POS)) {
            $groupOne[] = array(
                              "active" => $this->isActive("zolagopos"),
                              "icon"	 => "icon-home",
                              "label"	 => $this->__('POS'),
                              "url"	 => $this->getUrl('udropship/pos')
                          );
        }

        if($this->isModuleActive('zolagosizetable') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_VENDOR_SIZETABLE)) {
            $groupOne[] = array(
                              "active" => $this->isActive("zolagosizetable"),
                              "icon"	 => "icon-table",
                              "label"	 => Mage::helper('zolagosizetable')->__('Size tables'),
                              "url"	 => $this->getUrl('udropship/sizetable')
                          );
        }


        if (
            $this->isModuleActive('ghapi')
            && $this->isAllowed("udropship/ghapi")
            && ($this->getSession()->getVendor()->getGhapiVendorAccessAllow() == 1)
        ) {
            $groupOne[] = array(
                              "active" => $this->isActive("ghapi"),
                              "icon" => "icon-dashboard",
                              "label" => $this->__('GH API'),
                              "url" => $this->getUrl('udropship/ghapi')
                          );
        }


        if (
            $this->isModuleActive('ZolagoOs_IAIShop')
            && $this->isAllowed("iaishop/settings")
            && ($this->getSession()->getVendor()->getGhapiVendorAccessAllow() == 1)
        ) {
            $groupOne[] = array(
                            "active" => $this->isActive("zolagoosiaishop"),
                            "icon" => "icon-shopping-cart",
                            "label" => $this->__('IAI-Shop settings'),
                            "url" => $this->getUrl('iaishop/settings')
                        );
        }

        $grouped = $this->_processGroups($groupOne);

        if(count($grouped)) {
            return array(
                       "label" => $this->__("Settings"),
                       "active" => $this->isActive(
                           array(
                               "preferences",
                               "zolagooperator",
                               "zolagopos",
                               "tiership_rates",
                               "zolagosizetable",
                               "vendorsettings_info",
                               "vendorsettings_shipping",
                               "vendorsettings_rma",
                               "ghapi",
                               "zolagoosiaishop"
                           )
                       ),
                       "icon" => "icon-wrench",
                       "url" => "#",
                       "children" => $grouped
                   );
        }

        return null;
    }

    /**
     * Get menu section for Billing and Statements
     * NOTE: ACL role for this is ROLE_BILLING_OPERATOR
     * @see Zolago_Operator_Model_Acl::__construct()
     * @return array|null
     */
    public function getStatementsSection() {
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper("zolagocommon");
		if ($this->isModuleActive('ghstatements')
			&& $this->isAllowed(Zolago_Operator_Model_Acl::RES_BILLING_AND_STATEMENTS)
			&& $commonHlp->useGalleryConfiguration()
		) {
            /** @var GH_Statements_Helper_Data $helper */
            $helper = Mage::helper('ghstatements');
            // Sledzenie salda / Balance tracking
            $groupOne[] = array(
                              "active"    => $this->isActive("statements-balance"),
                              "icon"      => "icon-usd",
                              "label"     => $helper->__("Balance tracking"),
                              "url"       => $this->getUrl('udropship/statements/balance'),
                          );
            /** @see GH_Statements_Dropship_StatementsController::balanceAction() */

            // Rozliczenia okresowe / Periodic statements
            $groupOne[] = array(
                              "active"    => $this->isActive("statements-periodic"),
                              "icon"      => "icon-usd",
                              "label"     => $helper->__("Periodic statements"),
                              "url"       => $this->getUrl('udropship/statements/periodic'),
                          );
            /** @see GH_Statements_Dropship_StatementsController::statementsAction() */

            // Faktury / Invoices
            $groupOne[] = array(
                              "active"    => $this->isActive("statements-invoices"),
                              "icon"      => "icon-usd",
                              "label"     => $helper->__("Invoices"),
                              "url"       => $this->getUrl('udropship/statements/invoices'),
                          );
            /** @see GH_Statements_Dropship_StatementsController::invoicesAction() */

            $grouped = $this->_processGroups($groupOne);

            if (count($grouped)) {
                return array(
                           "label"     => $helper->__("Billing and statements"),
                           "active"    => $this->isActive(array(
                                       "statements-balance",
                                       "statements-periodic",
                                       "statements-invoices",
                                   )),
                           "icon"      => "icon-usd",
                           "url"       => "#",
                           "children"  => $grouped
                       );
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function getProductSection() {

        $groupOne = array();
        $groupTwo = array();

        // Mass edit (zarzadzenie opisami produktow)
        if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_UDPROD_VENDOR_MASS)) {
            $groupOne[] = array(
                              "active" => $this->isActive("udprod_product"),
                              "label"	 => $this->__('Mass Actions'),
                              "icon"	 => "icon-list",
                              "url"	 => $this->getUrl('udprod/vendor_product')
                          );
        }

        // Mass image (zarzadzanie zdjeciami)
        if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_UDPROD_VENDOR_IMAGE)) {
            $groupOne[] = array(
                              "active"	=> $this->isActive("udprod_image"),
                              "label"		=> $this->__('Mass Image'),
                              "url"		=> $this->getUrl('udprod/vendor_image'),
                              "icon"		=> "icon-picture"
                          );
        }

        // Mass price (zarzadzanie cenami)
        if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_UDPROD_VENDOR_PRICE)) {
            $groupOne[] = array(
                              "active"	=> $this->isActive("udprod_price"),
                              "label"		=> $this->__('Mass Price'),
                              "url"		=> $this->getUrl('udprod/vendor_price'),
                              "icon"		=> "icon-euro"
                          );
        }

        // Attributes preview (przeglad atrybutow)
        if ($this->isModuleActive('Zolago_Catalog') && $this->isAllowed(Zolago_Operator_Model_Acl::RES_UDPROD_VENDOR_ATTRIBUTES)) {
            $groupOne[] = array(
                              "active"	=> $this->isActive("udprod_attributes"),
                              "label"		=> $this->__('Attribute preview'),
                              "url"		=> $this->getUrl('udprod/vendor_attributes'),
                              "icon"		=> "icon-tags"
                          );
        }

        $grouped = $this->_processGroups($groupOne, $groupTwo);

        if(count($grouped)) {

            return array(
                       "label"		=> $this->__("Products"),
                       "active"	=> $this->isActive(array("udprod", "udprod_mass", "udprod_image", "udprod_price", "udprod_product", "udprod_attributes")),
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
        if(count($groups)==1) {
            return current($groups);
        }
        $out = array();
        foreach($groups as $group) {
            if(is_array($group) && count($group)) {
                foreach($group as $item) {
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
     * @param string $module
     * @return bool
     */
    public function isModuleActive($module) {
        return Mage::helper('udropship')->isModuleActive($module) || Mage::helper('core')->isModuleEnabled($module);
    }

    /**
     * @param string|array $in
     * @return bool
     */
    public function isActive($in) {
        if(is_array($in)) {
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
     * Get witch page is currently active.
     * This should be specified in action function in controller
     * NOTE: This is set as second parameter in function _renderPage from udropship
     * @see ZolagoOs_OmniChannel_Controller_VendorAbstract::renderPage()
     *
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
     * @return ZolagoOs_OmniChannel_Model_Vendor
     */
    public function getVendor() {
        return $this->getSession()->getVendor();
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Session
     */
    public function getSession() {
        return Mage::getSingleton('udropship/session');
    }
}