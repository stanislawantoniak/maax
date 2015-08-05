<?php

class Zolago_Campaign_Block_Vendor_Campaign_Edit extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
    }
    public function _prepareLayout() {

        $this->_prepareProductsGrid();
        $this->_prepareBannersGrid();
        $this->_prepareForm();
        parent::_prepareLayout();
    }

    public function getVendor(){
        return Mage::getSingleton('udropship/session')->getVendor();
    }

    public function _prepareForm(){
        $helper = Mage::helper('zolagocampaign');

        /* @var $_zolagoDropshipHelper Zolago_Dropship_Helper_Data */
        $_zolagoDropshipHelper = Mage::helper("zolagodropship");

        $isLocalVendor = $_zolagoDropshipHelper->isLocalVendor();

        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("campaign/vendor/save", array("_secure" => true)));

        $values = $this->getModel()->getData();


        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General"),
            "icon_class" => "icon-cog"
        ));
        if ($isLocalVendor) {
            $landingPage = $form->addFieldset("landing_page", array(
                "legend" => $helper->__("Landing Page Configuration"),
                "icon_class" => "icon-desktop"
            ));
            $landingPage->addType("category_tree", "Zolago_Campaign_Varien_Data_Form_Element_Categorytree");

            if (!$this->isModelNew()) {
                $landing_page_category_id = isset($values["landing_page_category"]) ? $values["landing_page_category"] : 0;
                $categoryName = Mage::getModel("catalog/category")->load($landing_page_category_id)->getName();

                $landingPageHelper = Mage::helper("zolagocampaign/landingPage");
                $urlText = $landingPageHelper->getLandingPageUrl($this->getModel()->getId());
            }
        }

        $prices = $form->addFieldset("price", array(
            "legend" => $helper->__("Prices"),
            "icon_class" => "icon-euro"
        ));

        $general->addField("name", "text", array(
            "name" => "name",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Campaign name for internal use'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-6"
        ));
        $general->addField("name_customer", "text", array(
            "name" => "name_customer",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Campaign name for customers'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-6"
        ));
        $general->addField("type", "select", array(
            "name" => "type",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Type'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_type')->toOptionHash(),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-3"
        ));

        $general->addField("status", "select", array(
            "name" => "status",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Status'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_status')->toOptionHash(),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-3"
        ));


        if($isLocalVendor){


//            $landingPage->addField('is_landing_page', 'checkbox', array(
//                'label'     => $helper->__('Landing Page'),
//                'name'      => 'is_landing_page',
//                'onclick'   => 'this.value = this.checked ? 1 : 0;',
//                "label_wrapper_class" => "col-md-3",
//                "wrapper_class" => "col-md-3"
//            ));

            $general->addField("is_landing_page", "radios", array(
                "name" => "is_landing_page",
                "required" => false,
                "label" => $helper->__('Landing Page'),
                "values" => $landingPageSource = Mage::getSingleton('zolagocampaign/campaign_urltype')->toOptionArray(),
                "label_wrapper_class" => "col-md-3",
                "wrapper_class" => "col-md-9 radio-buttons",
            ));

            $landingPage->addField("landing_page_context", "radios", array(
                "name" => "landing_page_context",
                "required" => false,
                "label" => $helper->__('Context'),
                "values" => $landingPageSource = Mage::getSingleton('zolagocampaign/attribute_source_campaign_landingPageContext')->toOptionArray(),
                "label_wrapper_class" => "col-md-3",
                "wrapper_class" => "col-md-9 radio-buttons landing-page-config",
            ));

            $landingPage->addField("context_vendor_id", "select", array(
                "name" => "context_vendor_id",
                "required" => true,
                "class" => "form-control",
                "label" => $helper->__('Select Vendor'),
                "values" => $landingPageVendorContextSource = Mage::getSingleton('zolagocampaign/attribute_source_campaign_landingPageContext_vendor')->toOptionArray(),
                "label_wrapper_class" => "col-md-3",
                "wrapper_class" => "col-md-4 landing-page-config",
                "form_group_wrapper_class" => "hidden"
            ));

            $landingPage->addField("landing_page_category", "category_tree", array(
                "name" => "landing_page_category",
                "required" => false,
                "class" => "form-control",
                "label" => $helper->__('Category'),
                "label_wrapper_class" => "col-md-3",
                "wrapper_class" => "col-md-6 landing-page-config",
                "after_element_html" => !$this->isModelNew() ? '<div id="landing_page_category_text">'.$categoryName.'</div><div id="landing_page_category_url"><a target="_blank" href="'.$urlText.'">'.$urlText.'</a></div>' : '<div id="landing_page_category_text"></div><div id="landing_page_category_url"></div>'
            ));
        }
        $general->addField("campaign_url", "text", array(
            "name" => "campaign_url",
            "class" => "form-control",
            "required" => false,
            "label" => $helper->__('URL Key'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-6"
        ));

        $general->addField("date_from", "text", array(
            "name" => "date_from",
            "class" => "form-control datetimepicker col-md-2",
            "label" => $helper->__('Date from'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-5 datetimepicker-wrapper",
            "after_element_html" => '<label style="margin: 8px;"><i class="icon-calendar"></i></label>'
        ));

        $general->addField("date_to", "text", array(
            "name" => "date_to",
            "class" => "form-control datetimepicker col-md-2",
            "label" => $helper->__('Date to'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-5 datetimepicker-wrapper",
            "after_element_html" => '<label style="margin: 8px;"><i class="icon-calendar"></i></label>'
        ));


        // Websites
        $websiteOptions = array();
        foreach (Mage::app()->getWebsites() as $websiteId => $website) {
            $websiteOptions[] = array(
                "label" => $website->getName(),
                "value" => $website->getId()
            );
        }

        $general->addField("website_ids", "multiselect", array(
            "name" => "website_ids",
            "required" => true,
            "class" => "multiple",
            "label" => $helper->__('Websites'),
            "values" => $websiteOptions,
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-6"
        ));

        // Prices definition
        $priceSourceCode = Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_DISCOUNT_PRICE_SOURCE_CODE;
        $prices->addField($priceSourceCode, "select", array(
            "name" => $priceSourceCode,
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Special Price source'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_pricesource')->toOptionHash(),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-2"
        ));

        $percentCode = Zolago_Campaign_Model_Campaign::ZOLAGO_CAMPAIGN_DISCOUNT_CODE;
        $prices->addField($percentCode, "text", array(
            "name" => $percentCode,
            "required" => true,
            "class" => "form-control positiveInteger",
            "label" => $helper->__('Discount percent'),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-2"
        ));

        $prices->addField("campaign_products", "hidden", array(
            "name" => "campaign_products",
            "required" => false,
            "label" => $helper->__('Campaign Products')
        ));

        $prices->addField("strikeout_type", "radios", array(
            "name" => "strikeout_type",
            "required" => true,
            "label" => $helper->__('Strikeout price'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_strikeout')->toOptionArray(),
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-9 radio-buttons"
        ));




        //reformat date_from date_to
        if (!empty($values)) {
            $values['date_from'] = !empty($values['date_from']) ? date('d-m-Y H:i', strtotime($values['date_from'])) : $values['date_from'];
            $values['date_to'] = !empty($values['date_to']) ? date('d-m-Y H:i', strtotime($values['date_to'])) : $values['date_to'];

            $values["url_type"] = !empty($values['url_type']) ? $values['url_type'] : Zolago_Campaign_Model_Campaign_Urltype::TYPE_MANUAL_LINK;
        }


        $websiteIdsSelected = $this->getModel()->getAllowedWebsites();
        $productsSelected = $this->getCampaignProducts();

        $values = array_merge($values,
            array(
                'website_ids' => $websiteIdsSelected,
                "campaign_products" => $productsSelected
            )
        );
        if($isLocalVendor){
            $form->getElement('is_landing_page')->setIsChecked(!empty($values['is_landing_page']));
        }

        $form->setValues($values);
        $this->setForm($form);
    }


    public function _prepareBannersGrid() {
        $design = Mage::getDesign();
        $design->setArea("adminhtml");
        $block = $this->getLayout()
            ->createBlock("zolagocampaign/vendor_campaign_banner_grid", "vendor_campaign_banner_grid")
            ->setTemplate("zolagocampaign/dropship/campaign/banner/grid.phtml")
        ;
        $block->setParentBlock($this);
        $this->setBannersGrid($block);
        $design->setArea("frontend");
    }

    public function _prepareProductsGrid() {
        $design = Mage::getDesign();
        $design->setArea("adminhtml");
        $block = $this->getLayout()
            ->createBlock("zolagocampaign/vendor_campaign_product_grid", "vendor_campaign_product_grid")
            ->setTemplate("zolagocampaign/dropship/campaign/product/grid.phtml");

        $block->setParentBlock($this);
        $this->setProductsGrid($block);
        $design->setArea("frontend");
    }
    /**
     * @return array
     */
    public function getCampaignProducts(){
        return $this->getModel()->getCampaignProducts();
    }

    /**
     * @return array
     */
    public function getCampaignProductsInfo(){
        return $this->getModel()->getCampaignProductsInfo();
    }


    public function getAddNewBannerPath(){
        $campaignId = $this->getRequest()->getParam("id");
        return Mage::getUrl('banner/vendor/new', array('campaign_id' => $campaignId, "_secure" => true));
    }
    /**
     * @return Zolago_Campaign_Model_Campaign
     */
    public function getModel()
    {
        if (!Mage::registry("current_campaign")) {
            Mage::register("current_campaign", Mage::getModel("zolagocampaign/campaign"));
        }
        return Mage::registry("current_campaign");
    }

    public function isModelNew()
    {
        return $this->getModel()->isObjectNew();
    }
}