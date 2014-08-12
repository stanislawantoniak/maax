<?php

class Zolago_Banner_Block_Vendor_Banner_Edit extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
    }

    public function _prepareLayout()
    {
        $this->_prepareGrid();
        $this->_prepareForm();
        parent::_prepareLayout();
    }

    public function _prepareForm(){
        $type = $this->getRequest()->getParam('type',null);

        $helper = Mage::helper('zolagobanner');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("banner/vendor/save"));

        //Common edit banner fields
        $general = $form->addFieldset("general", array(
            "legend" => $helper->__("General")
        ));

        $general->addField("name", "text", array(
            "name" => "name",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Name')
        ));

        $general->addField("campaign_id", "select", array(
            "name" => "campaign_id",
            "required" => true,
            "class" => "form-control",
            "label" => $helper->__('Campaign'),
            "values" => Mage::getSingleton('zolagobanner/banner_campaign')->toOptionHash()
        ));
        //--Common edit banner fields

        //Additional banner fields depends on type
        $this->_completeForm($form, $type);

        $values = $this->getModel()->getData();
        $form->setValues($values);
        $this->setForm($form);
    }

    public function _completeForm(Zolago_Dropship_Model_Form $form, $type)
    {
        $helper = Mage::helper('zolagobanner');

        //fetch config
        $configPath = Zolago_Banner_Model_Banner_Type::BANNER_TYPES_CONFIG;
        $configValue = Mage::getStoreConfig($configPath);
        $typesConfig = json_decode($configValue);
        $data = array();
        foreach ($typesConfig as $typesConfigType) {
            if (
                Mage::getSingleton('zolagobanner/banner_type')->getTypCodeByTitle($typesConfigType->title) == $type
            ) {
                $data = $typesConfigType;
            }
        }

        return $form;
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