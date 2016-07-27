<?php

/**
 * Maps field builder
 */
class Zolago_Pos_Model_Form_Fieldset_Maps extends Zolago_Common_Model_Form_Fieldset_Abstract
{
    protected function _getHelper()
    {
        return Mage::helper('zolagopos');
    }

    protected function _addFieldShowOnMap()
    {
        $this->_fieldset->addField('show_on_map', 'checkbox', array(
            'name' => 'show_on_map',
            'label' => $this->_helper->__('Show POS on map'),
            'class' => 'toggle',
            //"wrapper_class" => "make-switch switch-small has-switch",
            "use_plugin" => "switch",
        ));

    }

    protected function _addFieldMapName()
    {
        $this->_fieldset->addField('map_name', 'text', array(
            'name' => 'map_name',
            'label' => $this->_helper->__('POS name on map'),
            'required' => true,
            'class' => 'form-control',
            "maxlength" => 250,
            "form_group_wrapper_class" => "map-fields"
        ));

    }

	protected function _addFieldMapNotes()
	{
		$this->_fieldset->addField('map_notes', 'text', array(
			'name' => 'map_notes',
			'label' => $this->_helper->__('Notes'),
			'class' => 'form-control',
			"maxlength" => 100,
			"form_group_wrapper_class" => "map-fields"
		));

	}

    protected function _addFieldMapPhone()
    {
        $this->_fieldset->addField('map_phone', 'text', array(
            'name' => 'map_phone',
            'label' => $this->_helper->__('Phone'),
            'required' => false,
            'class' => 'validate-phone-number form-control',
            "maxlength" => 50,
            "wrapper_class" => "col-md-4",
            "form_group_wrapper_class" => "map-fields"
        ));

    }

    protected function _addFieldMapLatitude()
    {
        $this->_fieldset->addField('map_latitude', 'text', array(
            'name' => 'map_latitude',
            'label' => $this->_helper->__('Latitude'),
            'required' => true,
            'class' => 'form-control',
            "maxlength" => 10,
            "wrapper_class" => "col-md-4",
            "form_group_wrapper_class" => "map-fields"
        ));

    }

    protected function _addFieldMapLongitude()
    {
        $this->_fieldset->addField('map_longitude', 'text', array(
            'name' => 'map_longitude',
            'label' => $this->_helper->__('Longitude'),
            'required' => true,
            'class' => 'form-control',
            "maxlength" => 10,
            "wrapper_class" => "col-md-4",
            "form_group_wrapper_class" => "map-fields"
        ));
    }
    protected function _addFieldMapTimeOpened()
    {
        $this->_fieldset->addField('map_time_opened', 'textareagh', array(
            'name' => 'map_time_opened',
            'label' => $this->_helper->__('Time opened'),
            'class' => 'form-control',
            "wrapper_class" => "col-md-8",
            "form_group_wrapper_class" => "map-fields",
            'rows' => 10,
            'cols' => 40
        ));

    }
}