<?php
class Zolago_Operator_Block_Dropship_Operator_Edit extends Mage_Core_Block_Template {

    protected function _construct() {
        parent::_construct();
        $helper = Mage::helper('zolagooperator');
        $form = Mage::getModel('zolagodropship/form');
        /* @var $form Zolago_Dropship_Model_Form */
        $form->setAction($this->getUrl("udropship/operator/save"));


        $contact = $form->addFieldset('contact', array('legend'=>$helper->__('Details')));

        $builder = Mage::getModel('zolagooperator/form_fieldset_details');
        $builder->setFieldset($contact);
        $builder->prepareForm(array(
                                  'email',
                                  'password',
                                  'confirmation',
                                  'is_active',
                                  'firstname',
                                  'lastname',
                                  'phone',
                              ));
        $fieldset = $builder->getFieldset();
        $fieldset->addField("dhl_label_type","select", array(
             "name" => "dhl_label_type",
             "label" => "DHL label type",
             "values" => Mage::getModel('orbashipping/system_source_carrier_dhl_label')->toOptionArray(),
             "class"  => "form-control",
          )
        );           
        $acl = $this->getModel()->getAcl();
        $roles = $form->addFieldset("privileges", array("legend"=>$helper->__('Privileges')));
        $roles->addField("roles", "multiselect", array(
                             "name"	 => "roles",
                             "class"  => "multiple",
                             "label"  => $helper->__('Roles'),
                             "values" => $acl::getAllRolesOptions()
                         ));

        // Allowed POS

        $posColection = Mage::getResourceModel("zolagopos/pos_collection");
        /* @var $posColection Zolago_Pos_Model_Resource_Pos_Collection */
        $posColection->
            addVendorFilter(Mage::getSingleton('udropship/session')->getVendor());

        $roles->addField("allowed_pos", "multiselect", array(
                             "name"	 => "allowed_pos",
                             "class"  => "multiple",
                             "label"  => $helper->__('Allowed POS'),
                             "values" => $posColection->toOptionArray()
                         ));

        if($this->getIsNew()) {
            $form->getElement("password")->setRequired(true);
            $form->getElement("password_confirm")->setRequired(true);
        }

        $form->setValues($this->getModel()->getData());
        $this->setForm($form);
    }

    public function getFormHtml() {
        return $this->getForm()->toHtml();
    }

    /**
     * @return Zolago_Operator_Model_Operator
     */
    public function getModel() {
        if(!Mage::registry("current_operator")) {
            Mage::register("current_operator", Mage::getModel("zolagooperator/operator"));
        }
        return Mage::registry("current_operator");
    }

    public function getIsNew() {
        return !(bool)$this->getModel()->getId();
    }


}

