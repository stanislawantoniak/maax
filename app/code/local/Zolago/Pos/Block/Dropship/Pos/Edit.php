<?php
class Zolago_Pos_Block_Dropship_Pos_Edit extends Mage_Core_Block_Template {
	
	protected function _construct() {
		parent::_construct();
		$helper = Mage::helper('zolagopos');
        $form = new Zolago_Dropship_Block_Form();

		
        $form->setAction($this->getUrl("udropship/pos/save"));
		
        $settings = $form->addFieldset('setting', array('legend'=>$helper->__('POS Settings')));
        
		$settings->addField("pos_id", "hidden", array("name"=>"pos_id"));
		
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
        
        $country = $this->getModel()->getCountryId();
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


		
        $form->setValues($this->getModel()->getData());
        $this->setForm($form);
	}

	public function getFormHtml() {
		return $this->getForm()->toHtml();
	}
	
	/**
	 * @return Zolago_Pos_Model_Pos
	 */
	public function getModel() {
		if(!Mage::registry("current_pos")){
			 Mage::register("current_pos", Mage::getModel("zolagopos/pos"));
		}
		return Mage::registry("current_pos");
	}
	
	public function getIsNew() {
		return $this->getModel()->getId();
	}
	
	
}

