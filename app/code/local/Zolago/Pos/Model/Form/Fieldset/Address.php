<?php
/**
 * address field builder
 */
class Zolago_Pos_Model_Form_Fieldset_Address extends Zolago_Common_Model_Form_Fieldset_Abstract 
{
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }
    protected function _addFieldCity() {
        $this->_fieldset->addField('city', 'text', array(
                                       'name'          => 'city',
                                       'label'         => $this->_helper->__('City'),
                                       'required'      => true,
                                       "maxlength"     => 100
                                   ));

    }
    protected function _addFieldCountryId() {
        $this->_fieldset->addField('country_id', 'select', array(
                                       'name'          => 'country_id',
                                       'label'         => $this->_helper->__('Country'),
                                       'values'        => Mage::getSingleton("adminhtml/system_config_source_country")->toOptionArray(),
                                       'required'      => true,
                                   ));

    }
    protected function _addFieldRegionId() {
        $country = $this->_model->getCountryId();
        $regionOpts = array();

        if($country) {
            $country = Mage::getModel("directory/country")->load($country);
            /* var $country Mage_Directory_Model_Country */
            foreach($country->getRegionCollection() as $region) {
                $regionOpts[] = array(
                                    "value" => $region->getId(),
                                    "label" => $region->getName()
                                );
            }
            array_unshift($regionOpts, array("value"=>"", "label"=>Mage::helper("adminhtml")->__("-- Please select --")));
        }
        $this->_fieldset->addField('region_id', 'select', array(
                                       'name'          => 'region_id',
                                       'label'         => $this->_helper->__('Region'),
                                       'class'         => 'countries',
                                       'values'        => $regionOpts
                                   ));

    }
    protected function _addFieldStreet() {
        $this->_fieldset->addField('street', 'text', array(
                                       'name'          => 'street',
                                       'label'         => $this->_helper->__('Street and number'),
                                       'required'      => true,
                                       "maxlength"     => 150
                                   ));

    }
    protected function _addFieldPostcode() {
        $this->_fieldset->addField('postcode', 'text', array(
                                       'name'          => 'postcode',
                                       'label'         => $this->_helper->__('Postcode'),
                                       'required'      => true,
                                       "maxlength"     => 6
                                   ));


    }
    protected function _addFieldCompany() {
        $this->_fieldset->addField('company', 'text', array(
                                       'name'          => 'company',
                                       'label'         => $this->_helper->__('Company'),
                                       "maxlength"     => 100
                                   ));


    }

}