<?php

class Zolago_Banner_Block_Vendor_Banner_Edit extends Mage_Core_Block_Template
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
        $helper = Mage::helper('zolagobanner');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("banner/vendor/save"));

        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General")
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
            "values" => Mage::getSingleton('zolagobanner/banner_type')->toOptionHash()
        ));

        $general->addField("status", "select", array(
            "name" => "status",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Status'),
            "values" => Mage::getSingleton('zolagobanner/banner_status')->toOptionHash()
        ));

        // Websites
        $websiteOptions = array();
        foreach (Mage::app()->getWebsites() as $websiteId => $website) {
            $websiteOptions[] = array(
                "label" => $website->getName(),
                "value" => $website->getId()
            );
        }

        $values = $this->getModel()->getData();
        $form->setValues($values);
        $this->setForm($form);
    }
    public function _prepareGrid() {
        $design = Mage::getDesign();
        $design->setArea("adminhtml");

        $design->setArea("frontend");
    }

    /**
     * @return Zolago_Banner_Model_Banner
     */
    public function getModel()
    {
        if (!Mage::registry("current_banner")) {
            Mage::register("current_banner", Mage::getModel("zolagobanner/banner"));
        }
        return Mage::registry("current_banner");
    }

    public function isModelNew()
    {
        return $this->getModel()->isObjectNew();
    }
}