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

    public function _prepareForm(){
        $helper = Mage::helper('zolagocampaign');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("campaign/vendor/save"));

        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General"),
            "icon_class" => "icon-cog"
        ));

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
        $general->addField("url_type", "radios", array(
            "name" => "url_type",
            "required" => true,
            "label" => $helper->__('URL type'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_urltype')->toOptionArray(),
            "value" => Zolago_Campaign_Model_Campaign_Urltype::TYPE_MANUAL_LINK,
            "label_wrapper_class" => "col-md-3",
            "wrapper_class" => "col-md-8 radio-buttons"
        ));
        $general->addField("url_key", "text", array(
            "name" => "url_key",
            "required" => true,
            "class" => "form-control urlKeyFormat urlKeyExists",
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

        $values = $this->getModel()->getData();


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
        return Mage::getUrl('banner/vendor/new', array('campaign_id' => $campaignId));
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