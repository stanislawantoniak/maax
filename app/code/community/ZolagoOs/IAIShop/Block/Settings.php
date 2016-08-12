<?php


class ZolagoOs_IAIShop_Block_Settings extends Mage_Core_Block_Template
{
    protected $_carriers;

    protected function _construct() {
        parent::_construct();
        $helper = Mage::helper('zosiaishop');
        $form = Mage::getModel('zosiaishop/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("iaishop/settings/save"));

        $loginData = $form->addFieldset('loginData', array('legend'=>$helper->__('IAI-Shop API Login Data')));
        $builder = Mage::getModel('zosiaishop/form_fieldset_details');
        $builder->setFieldset($loginData);
        $builder->prepareForm(array(
            'id',
            'url',
            'login',
            'pass'
        ));

        /*
        $delivery = $form->addFieldset('delivery', array('legend'=>$helper->__('IAI-Shop Delivery')));
        foreach ($this->getCarriers() as $d) $delivery->addField($d->getCarrierCode(), "select", array(
            "name"	 => "delivery_" . $d->getCarrierCode(),
            "class"  => "form-control",
            "label"  => $helper->__($d->getConfigData('title')),
            "values" => $this->_getDelivery()
        ));


        if($this->getIsNew()){
            $form->getElement("pass")->setRequired(true);
            $form->getElement("pass")->setAfterElementHtml("");
        }

        */
        $_session = Mage::getSingleton('udropship/session');

        $data = $_session->getFormData();
        $check = ['id', 'url', 'login'];
        foreach ($check as $c) if (!isset($data[$c])) $data[$c] = $_session->getVendor()->getData("iaishop_" . $c);

        foreach ($this->getCarriers() as $d)
            if (!isset($data[$d->getCarrierCode()]))
                $data[$d->getCarrierCode()] =  [0 => $_session->getVendor()->getData("iaishop_delivery_" . $d->getCarrierCode())];

        $form->setValues($data);
        $this->setForm($form);
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('udropship/session');
    }

    public function getFormAction() {
        return $this->getUrl('iaishop/settings/save', array("_secure" => true));
    }


    public function getFormHtml() {
        return $this->getForm()->toHtml();
    }

    protected function _getDelivery()
    {
        return ["",
            "DPD by IAI Koperta",
            "DPD by IAI Paczka",
            "DPD by IAI Paleta",
            "K-EX by IAI Koperta",
            "K-EX by IAI Paczka",
            "Odbiór osobisty",
            "Paczka w RUCHu by IAI",
            "Paczkomaty InPost by IAI"];
    }

    public function getCarriers()
    {
        if (!$this->_carriers) {
            $this->_carriers = array();

            $existing = array(
                array("delivery_code" => Orba_Shipping_Model_Packstation_Inpost::CODE),
                array("delivery_code" => Orba_Shipping_Model_Post::CODE)
            );

            /** @var Mage_Shipping_Model_Config $shippingConfig */
            $shippingConfig = Mage::getSingleton('shipping/config');

            foreach ($existing as $c) {
                $ins = $shippingConfig->getCarrierInstance($c["delivery_code"]);
                if ($ins) array_push($this->_carriers, $ins);
            }
        }
        return $this->_carriers;
    }

    public function getIsNew() {
        return !(bool)Mage::getSingleton('udropship/session')->getVendor()->getIaishopId();
    }
}
