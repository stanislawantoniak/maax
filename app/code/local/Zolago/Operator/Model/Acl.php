<?php

class Zolago_Operator_Model_Acl extends Zend_Acl
{

    // Roles definiton
    const ROLE_ORDER_OPERATOR						= "order_operator";
    const ROLE_MARKETING_OFFICER					= "marketing_officer";
    const ROLE_RMA_OPERATOR							= "rma_operator";
    const ROLE_HELPDESK								= "helpdesk";
    const ROLE_MASS_OPERATOR						= "mass_operator";
    const ROLE_PRODUCT_OPERATOR						= "product_operator";
    const ROLE_PAYMENT_OPERATOR						= "payment_operator";
    const ROLE_GHAPI_OPERATOR						= "ghapi_operator";
    const ROLE_BILLING_OPERATOR		                = "billing_operator";
    const ROLE_SUPERUSER_OPERATOR		            = "superuser_operator";
    const ROLE_LOYALTY_CARD_OPERATOR				= "loyalty_card_operator";

    // Resource definition

    // Vendor controller for all
    const RES_UDROPSHIP_VENDOR_SET_LOCALE			= "udropship/vendor/setlocale";
    const RES_UDROPSHIP_VENDOR_WYSIWYG				= "udropship/vendor/wysiwyg";
    const RES_UDROPSHIP_VENDOR_DASHBOARD			= "udropship/vendor/dashboard";
    const RES_UDROPSHIP_VENDOR_INDEX				= "udropship/vendor/index";
    const RES_UDROPSHIP_VENDOR_LOGIN				= "udropship/vendor/login";
    const RES_UDROPSHIP_VENDOR_LOGOUT				= "udropship/vendor/logout";
    const RES_UDROPSHIP_VENDOR_PASSWORD				= "udropship/vendor/password";
    const RES_UDROPSHIP_VENDOR_PASSWORD_POST		= "udropship/vendor/passwordPost";
    const RES_UDROPSHIP_VENDOR_EDIT_PASSWORD		= "udropship/vendor/editPassword";
    const RES_UDROPSHIP_VENDOR_EDIT_PASSWORD_POST   = "udropship/vendor/savePassword";

    // Po Vendor controller - whole
    const RES_UDPO_VENDOR							= "udpo/vendor";
    // Po Vendor aggregated
    const RES_UDPO_VENDOR_AGGREGATED				= "udpo/vendor_aggregated";
    // Po print waybills
    const RES_ORBASHIPPING						= "orbashipping";
    // Po Vendor controller - whole
    const RES_URMA_VENDOR							= "urma/vendor";
    // Po Vendor controller - whole
    const RES_ASK_QUESTION							= "udqa/vendor";
    // Product editor
    const RES_UDPROD_VENDOR							= "udprod/vendor";
    // Mass editor
    const RES_UDPROD_VENDOR_IMAGE					= "udprod/vendor_image";
    // Mass editor
    const RES_UDPROD_VENDOR_AJAX					= "udprod/vendor_ajax";
    // Mass editor
    const RES_UDPROD_VENDOR_MASS					= "udprod/vendor_mass";
    // Gh Attribute Rules
    const RES_GH_ATTRIBUTE_RULES					= "udropship/mass";

    // Mass editor
    const RES_UDPROD_VENDOR_PRODUCT					= "udprod/vendor_product";
    // POS Manage
    const RES_UDROPSHIP_POS							= "udropship/pos";
    // Operator manage
    const RES_UDROPSHIP_OPERATOR					= "udropship/operator";
    // Tiership manage
    const RES_UTIERSHIP_OPERATOR					= "udtiership/vendor";

    // Campaign management
    const RES_CAMPAIGN_VENDOR                       = "campaign/vendor";
    const RES_CAMPAIGN_PLACEMENT                    = "campaign/placement";
    const RES_CAMPAIGN_PLACEMENT_IN_CATEGORIES      = "campaign/placement_category";
    const RES_BANNER                                = "banner/vendor";
    const RES_BUDGET_MARKETING						= "udropship/marketing";

    const RES_LOYALTY_CARD							= "loyalty/card";

    // Overpayments management
    const RES_PAYMENT_OPERATOR						= "udpo/payment";

    // GH API Access
    const RES_GHAPI_OPERATOR						= "udropship/ghapi";

	// IAI-Shop API Access
	const RES_IAISHOP_OPERATOR						= "iaishop/settings";

    // Attribute preview
    const RES_UDPROD_VENDOR_ATTRIBUTES              = "udprod/vendor_attributes";

    // Price management
    const RES_UDPROD_VENDOR_PRICE                   = "udprod/vendor_price";
    const RES_UDPROD_VENDOR_PRICE_DETAIL            = "udprod/vendor_price_detail";

    // Billing and statements
    const RES_BILLING_AND_STATEMENTS                = "udropship/statements";
    // settings
    const RES_VENDOR_SETTINGS						= "udropship/vendor_settings";
    // sizetable
    const RES_VENDOR_SIZETABLE						= "udropship/sizetable";
    const RES_VENDOR_RULES							= "udropship/vendor/rules";
    const RES_VENDOR_RULES_DOCUMENT					= "udropship/vendor/getDocument";
    // Resources as array
    protected static $_currentResources = array(
            self::RES_UDROPSHIP_VENDOR_SET_LOCALE		=> "Vendor Set locale",
            self::RES_UDROPSHIP_VENDOR_WYSIWYG			=> "Vendor wysiwyg",
            self::RES_UDROPSHIP_VENDOR_DASHBOARD		=> "Vendor dashboard",
            self::RES_UDROPSHIP_VENDOR_INDEX			=> "Vendor index",
            self::RES_UDROPSHIP_VENDOR_LOGIN			=> "Vendor login",
            self::RES_UDROPSHIP_VENDOR_LOGOUT			=> "Vendor logout",
            self::RES_UDROPSHIP_VENDOR_PASSWORD			=> "Vendor pasword",
            self::RES_UDROPSHIP_VENDOR_PASSWORD_POST	=> "Vendor password post",
            self::RES_UDROPSHIP_VENDOR_EDIT_PASSWORD    => "Edit Password",
            self::RES_UDROPSHIP_VENDOR_EDIT_PASSWORD_POST=> "Edit Password post",
            // PO
            self::RES_UDPO_VENDOR						=> "Orders",
            self::RES_UDPO_VENDOR_AGGREGATED			=> "Dispatch refs",
            self::RES_ORBASHIPPING				=> "Print waybills",
            // RMA
            self::RES_URMA_VENDOR						=> "RMA",
            // Ask Question
            self::RES_ASK_QUESTION						=> "Vendor ask question",
            // Product editor
            self::RES_UDPROD_VENDOR						=> "Product edit",
            // Mass edit
            self::RES_UDPROD_VENDOR_MASS				=> "Mass products",

            // Gh Attribute Rules
            self::RES_GH_ATTRIBUTE_RULES				=> "GH_ATTRIBUTE_RULES",
            // Mass images
            self::RES_UDPROD_VENDOR_PRODUCT				=> "Mass products v2",
            // Mass images
            self::RES_UDPROD_VENDOR_IMAGE				=> "Mass images",
            // Mass images
            self::RES_UDPROD_VENDOR_AJAX				=> "Mass images",
            // POS Manage
            self::RES_UDROPSHIP_POS						=> "POS Manage",
            // Operator manage
            self::RES_UDROPSHIP_OPERATOR				=> "Operator manage",
            // Campaign management
            self::RES_CAMPAIGN_VENDOR                   => "Campaign management",
            self::RES_CAMPAIGN_PLACEMENT                => "Campaign placement management",
            self::RES_CAMPAIGN_PLACEMENT_IN_CATEGORIES  => "Campaign placement in categories management",
            self::RES_BANNER                            => "Banners management",
            self::RES_BUDGET_MARKETING					=> "Budget marketing costs",
            // Overpayments management
            self::RES_PAYMENT_OPERATOR                  => "Payment manage",
            // GH API Access
            self::RES_GHAPI_OPERATOR                    => "GH API",
            // Attribute preview
            self::RES_UDPROD_VENDOR_ATTRIBUTES          => "Attribute preview",
            // Price management
            self::RES_UDPROD_VENDOR_PRICE               => "Price management",
            self::RES_UDPROD_VENDOR_PRICE_DETAIL        => "Price detail management",
            // Billing and statements
            self::RES_BILLING_AND_STATEMENTS            => "Billing and statements",
            self::RES_VENDOR_SETTINGS					=> "Vendor settings",
            self::RES_VENDOR_SIZETABLE					=> "Sizetable settings",
            self::RES_VENDOR_RULES						=> "Regulations",
            self::RES_VENDOR_RULES_DOCUMENT				=> "Get regulations documents",

            // IAI-Shop Access
	    self::RES_IAISHOP_OPERATOR                  => "IAI-Shop API",
            self::RES_LOYALTY_CARD						=> "Loyalty card"
                                          );

    public function __construct() {
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::getSingleton('udropship/session')->getVendor();
        /** @var Zolago_Common_Helper_Data $commonHlp */
        $commonHlp = Mage::helper("zolagocommon");
        $isGallery = $commonHlp->useGalleryConfiguration();
        $type = self::TYPE_ALLOW;
        if (!$isGallery) {
            $type = self::TYPE_DENY;
        }
        // Set resources
        foreach(array_keys(self::$_currentResources) as $resourceCode) {
            $this->addResource($resourceCode);
        }
        // Set roles
        foreach(array_keys(self::getAllRoles()) as $roleCode) {
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
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_EDIT_PASSWORD);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, null, self::RES_UDROPSHIP_VENDOR_EDIT_PASSWORD_POST);

        // Build ACL Rules - Order operator
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_ORDER_OPERATOR, self::RES_UDPO_VENDOR);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_ORDER_OPERATOR, self::RES_UDPO_VENDOR_AGGREGATED);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_ORDER_OPERATOR, self::RES_ORBASHIPPING);

        // Build ACL Rules - Marketing officer
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_CAMPAIGN_VENDOR);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_CAMPAIGN_PLACEMENT);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_CAMPAIGN_PLACEMENT_IN_CATEGORIES);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_BANNER);
        if ($vendor->getMarketingChargesEnabled()) {
            $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MARKETING_OFFICER, self::RES_BUDGET_MARKETING);
        } else {
            $this->setRule(self::OP_ADD, self::TYPE_DENY, self::ROLE_MARKETING_OFFICER, self::RES_BUDGET_MARKETING);
        }

        // Build ACL Rules - RMA Operator
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_RMA_OPERATOR, self::RES_URMA_VENDOR);

        // Build ACL Rules - Helpdesk
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_HELPDESK, self::RES_ASK_QUESTION);

        // Build ACL Rules - Product operator; price edit (zarzadzanie cenami)
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_PRODUCT_OPERATOR, self::RES_UDPROD_VENDOR_PRICE); // zarzadzanie cenami
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_PRODUCT_OPERATOR, self::RES_UDPROD_VENDOR_PRICE_DETAIL); // szczegoly cen
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_PRODUCT_OPERATOR, self::RES_UDPROD_VENDOR);

        // Build ACL Rules - Mass Actions (Zarządzanie opisami i zdjęciami produktów + przeglad atrybutow)
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MASS_OPERATOR, self::RES_UDPROD_VENDOR_IMAGE);  // zarzadzanie zdjeciami produktow
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MASS_OPERATOR, self::RES_UDPROD_VENDOR_AJAX);  // zarzadzanie zdjeciami produktow
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MASS_OPERATOR, self::RES_UDPROD_VENDOR_PRODUCT);// zarzadzanie opisami produktow
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MASS_OPERATOR, self::RES_UDPROD_VENDOR_MASS);   // zarzadzanie opisami produktow ?
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MASS_OPERATOR, self::RES_UDPROD_VENDOR_ATTRIBUTES); //przeglad atrybutow

        // Build ACL Rules - Overpayments management
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_PAYMENT_OPERATOR, self::RES_PAYMENT_OPERATOR);

        // Build ACL Rules - GH API Access
        if ($vendor->getData('ghapi_vendor_access_allow')) {
            $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_GHAPI_OPERATOR, self::RES_GHAPI_OPERATOR);
			$this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_GHAPI_OPERATOR, self::RES_IAISHOP_OPERATOR);
        } else {
            $this->setRule(self::OP_ADD, self::TYPE_DENY, self::ROLE_GHAPI_OPERATOR, self::RES_GHAPI_OPERATOR);
			$this->setRule(self::OP_ADD, self::TYPE_DENY, self::ROLE_GHAPI_OPERATOR, self::RES_IAISHOP_OPERATOR);
        }

        // Build ACL Rule - autofill attributes by apply rule - masowa zmiana cech produków
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_MASS_OPERATOR, self::RES_GH_ATTRIBUTE_RULES);

        // Build ACL Rules - Billing and statements
        if ($isGallery) {
            $this->setRule(self::OP_ADD, $type           , self::ROLE_BILLING_OPERATOR, self::RES_BILLING_AND_STATEMENTS);
        }
        // superuser
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_SUPERUSER_OPERATOR, self::RES_VENDOR_SETTINGS);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_SUPERUSER_OPERATOR, self::RES_VENDOR_SIZETABLE);
        $this->setRule(self::OP_ADD, $type           , self::ROLE_SUPERUSER_OPERATOR, self::RES_VENDOR_RULES);
        $this->setRule(self::OP_ADD, $type           , self::ROLE_SUPERUSER_OPERATOR, self::RES_VENDOR_RULES_DOCUMENT);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_SUPERUSER_OPERATOR, self::RES_UDROPSHIP_POS);
        $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_SUPERUSER_OPERATOR, self::RES_UDROPSHIP_OPERATOR);

        // Build ACL rule - LOYALTY CARD management
        if ($commonHlp->useLoyaltyCardSection()) {
            $this->setRule(self::OP_ADD, self::TYPE_ALLOW, self::ROLE_LOYALTY_CARD_OPERATOR, self::RES_LOYALTY_CARD);
        }
    }

    /**
     * @return array
     */
    public static function getAllRoles() {
        /** @var Zolago_Common_Helper_Data $commonHlp */
        $commonHlp = Mage::helper("zolagocommon");
        $isGallery = $commonHlp->useGalleryConfiguration();
        $_currentRoles = array(
                             self::ROLE_ORDER_OPERATOR					=> "Order operator",
                             self::ROLE_MARKETING_OFFICER				=> "Marketing officer",
                             self::ROLE_RMA_OPERATOR						=> "RMA Operator",
                             self::ROLE_HELPDESK							=> "Helpdesk",
                             self::ROLE_MASS_OPERATOR					=> "Mass Operator",
                             self::ROLE_PRODUCT_OPERATOR					=> "Product Operator",
                             self::ROLE_PAYMENT_OPERATOR					=> "Payment manage",
                             self::ROLE_GHAPI_OPERATOR					=> "GH API Settings",
                         );
        if ($isGallery) {
            $_currentRoles[self::ROLE_SUPERUSER_OPERATOR] = "Configuration and regulations";
            $_currentRoles[self::ROLE_BILLING_OPERATOR]   = "Billing and statements";
        } else {
            $_currentRoles[self::ROLE_SUPERUSER_OPERATOR] = "Configuration";
        }
        if ($commonHlp->useLoyaltyCardSection()) {
            $_currentRoles[self::ROLE_LOYALTY_CARD_OPERATOR]   = "Loyalty cards";
        }
        return $_currentRoles;
    }

    public static function getAllRolesOptions() {
        $out = array();
        foreach(self::getAllRoles() as $value=>$label) {
            $out[] = array(
                         "label"	=>	Mage::helper('zolagooperator')->__($label),
                         "value" =>	$value
                     );
        }
        return $out;
    }
}
