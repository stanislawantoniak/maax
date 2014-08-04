<?php

class Zolago_Campaign_Block_Vendor_Campaign_Edit extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
    }
    public function _prepareLayout() {

        $this->_prepareGrid();
        $this->_prepareForm();
        parent::_prepareLayout();
    }

    public function _prepareForm(){
        $helper = Mage::helper('zolagocampaign');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("campaign/vendor/save"));

        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General")
        ));

        $prices = $form->addFieldset("price", array(
            "legend" => $helper->__("Prices")
        ));

        $general->addField("name", "text", array(
            "name" => "name",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Name')
        ));

        $general->addField("type", "select", array(
            "name" => "type",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Type'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_type')->toOptionHash()
        ));

        $general->addField("status", "select", array(
            "name" => "status",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Status'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_status')->toOptionHash()
        ));

        $general->addField("url_key", "text", array(
            "name" => "url_key",
            "required" => true,
            "class" => "form-control urlKeyFormat urlKeyExists",
            "label" => $helper->__('URL Key')
        ));

        $general->addField("date_from", "text", array(
            "name" => "date_from",
            "class" => "form-control datetimepicker",
            "wrapper_class" => "col-md-3",
            "label" => $helper->__('Date from'),
        ));

        $general->addField("date_to", "text", array(
            "name" => "date_to",
            "class" => "form-control datetimepicker",
            "wrapper_class" => "col-md-3",
            "label" => $helper->__('Date to'),
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
            "values" => $websiteOptions
        ));
        $prices->addField("campaign_products", "hidden", array(
            "name" => "campaign_products",
            "required" => false,
            "class" => "form-control",
            "label" => $helper->__('Campaign Products')
        ));

        // Prices definition

        $prices->addField("price_source_id", "select", array(
            "name" => "price_source_id",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Price source'),
            "values" => Mage::getSingleton('zolagocampaign/campaign_pricesource')->toOptionHash()
        ));

        $prices->addField("percent", "text", array(
            "name" => "percent",
            "required" => true,
            "class" => "form-control positiveInteger",
            "label" => $helper->__('Discount percent')
        ));

        $prices->addField("price_srp", "text", array(
            "name" => "price_srp",
            "class" => "form-control numeric",
            "label" => $helper->__('Price SRP')
        ));

        $values = $this->getModel()->getData();

        $values = array_merge($values,
            array(
                'date_from_unformat' => date("m/d/Y H:i:s",strtotime($values['date_from'])),
                "date_to_unformat" => date("m/d/Y H:i:s",strtotime($values['date_to']))
            )
        );
        //reformat date_from date_to
        if (!empty($values)) {
            $values['date_from'] = !empty($values['date_from']) ? date('d-m-Y H:i', strtotime($values['date_from'])) : $values['date_from'];
            $values['date_to'] = !empty($values['date_to']) ? date('d-m-Y H:i', strtotime($values['date_to'])) : $values['date_to'];
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
    public function _prepareGrid() {
        $design = Mage::getDesign();
        $design->setArea("adminhtml");
        $block = $this->getLayout()
            ->createBlock("zolagocampaign/vendor_campaign_product_grid", "vendor_campaign_product_grid")
            ->setTemplate("zolagocampaign/dropship/campaign/product/grid.phtml")
        ;
        $block->setParentBlock($this);
        $this->setGrid($block);
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

    public function getRemoveProductPath()
    {
        return Mage::getBaseUrl() . "campaign/vendor/removeProduct";
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