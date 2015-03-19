<?php
class GH_Api_Block_Dropship_Settings extends Mage_Core_Block_Template {
    protected function _construct()
    {
        parent::_construct();
    }
    public function _prepareLayout() {
        $this->_prepareForm();
        parent::_prepareLayout();
    }
    public function _prepareForm(){
        $helper = Mage::helper('ghapi');
        $form = Mage::getModel('zolagodropship/form');

        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("ghapi/dropship/ghapi/save"));

        $general = $form->addFieldset("login_data", array(
            "legend" => $helper->__("Login Data")
        ));

        $general->addField("ghapi_vendor_id", "text", array(
            "name" => "ghapi_vendor_id",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Vendor ID')
        ));
        $general->addField("ghapi_vendor_password", "password", array(
            "name" => "ghapi_vendor_password",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('Password')
        ));
        $general->addField("ghapi_vendor_api_key", "text", array(
            "name" => "ghapi_vendor_api_key",
            "class" => "form-control",
            "required" => true,
            "label" => $helper->__('API Key')
        ));

        //$form->setValues($values);
        $this->setForm($form);
    }
}