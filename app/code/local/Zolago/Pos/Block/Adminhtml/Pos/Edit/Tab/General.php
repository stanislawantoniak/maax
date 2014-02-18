<?php
class Zolago_Pos_Block_Adminhtml_Pos_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{


    public function canShowTab() {
        return 1;
    }

    public function getTabLabel() {
        return Mage::helper('zolagopos')->__("General");
    }

    public function getTabTitle() {
        return Mage::helper('zolagopos')->__("General POS Information");
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareForm()
    {
        $helper = Mage::helper('zolagopos');
        $form = new Varien_Data_Form();
        
        $settings = $form->addFieldset('setting', array('legend'=>$helper->__('POS Settings')));
        
        $settings->addField('name', 'text', array(
            'name'          => 'name',
            'label'         => $helper->__('Name'),
            'required'      => true,
            "maxlength"     => 100
        ));
        
        $settings->addField('is_active', 'select', array(
            'name'          => 'is_active',
            'label'         => $helper->__('Is active'),
            'required'      => true,
            'options'       => Mage::getSingleton("adminhtml/system_config_source_yesno")->toArray()
        ));
        
        $settings->addField('vendor_owner_id', 'select', array(
            'name'          => 'vendor_owner_id',
            'label'         => $helper->__('Vendor owner'),
            'values'        => Mage::getSingleton("udropship/vendor_source")->getAllOptions(),
        ));
        
        $settings->addField('minimal_stock', 'text', array(
            'name'          => 'minimal_stock',
            'label'         => $helper->__('Minimal stock'),
            'required'      => true,
            "class"         => "validate-digits",
            "maxlength"     => 3
        ));
		
        $settings->addField('priority', 'text', array(
            'name'          => 'priority',
            'label'         => $helper->__('Priority'),
            'required'      => true,
            "class"         => "validate-digits",
            "maxlength"     => 3
        ));
        
        $settings->addField('external_id', 'text', array(
            'name'          => 'external_id',
            'label'         => $helper->__('External ID'),
            "maxlength"     => 100
        ));
        
        $settings->addField('client_number', 'text', array(
            'name'          => 'client_number',
            'label'         => $helper->__('Client number'),
            "maxlength"     => 100
        ));
        
        
        $address = $form->addFieldset('address', array('legend'=>$helper->__('Address')));
        
        
        $address->addField('city', 'text', array(
            'name'          => 'city',
            'label'         => $helper->__('City'),
            'required'      => true,
            "maxlength"     => 100
        ));
        
        $address->addField('country_id', 'select', array(
            'name'          => 'country_id',
            'label'         => $helper->__('Country'),
            'values'        => Mage::getSingleton("adminhtml/system_config_source_country")->toOptionArray(),
            'required'      => true,
        ));
        
        $country = $this->_getModel()->getCountryId();
        $regionOpts = array();
        
        if($country){
            $country = Mage::getModel("directory/country")->load($country);
            /* var $country Mage_Directory_Model_Country */
            foreach($country->getRegionCollection() as $region){
                $regionOpts[] = array(
                    "value" => $region->getId(),
                    "label" => $region->getName()
                );
            }
            array_unshift($regionOpts, array("value"=>"", "label"=>Mage::helper("adminhtml")->__("-- Please select --")));
        }
        $address->addField('region_id', 'select', array(
            'name'          => 'region_id',
            'label'         => $helper->__('Region'),
            'class'         => 'countries',
            'values'        => $regionOpts
        ));
        
        
        $address->addField('street', 'text', array(
            'name'          => 'street',
            'label'         => $helper->__('Street and number'),
            'required'      => true,
            "maxlength"     => 150
        ));
        
        $address->addField('postcode', 'text', array(
            'name'          => 'postcode',
            'label'         => $helper->__('Postcode'),
            'required'      => true,
            "maxlength"     => 6
        ));
        
        $address->addField('company', 'text', array(
            'name'          => 'company',
            'label'         => $helper->__('Company'),
            "maxlength"     => 100
        ));
        
        $contact = $form->addFieldset('contact', array('legend'=>$helper->__('Contact')));
        
        $contact->addField('phone', 'text', array(
            'name'          => 'phone',
            'label'         => $helper->__('Phone'),
            'required'      => true,
            'class'         => 'validate-phone-number',
            "maxlength"     => 50
        ));
        $contact->addField('email', 'text', array(
            'name'          => 'email',
            'label'         => $helper->__('Email'),
            'class'         => 'validate-email',
            "maxlength"     => 100
        ));
        
        $form->setValues($this->_getValues());
        
        $this->setForm($form);
    }
    
    protected function _getValues() {
        return $this->_getModel()->getData();
    }
    
    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _getModel() {
        return Mage::registry('zolagopos_current_pos');
    }
    
}
