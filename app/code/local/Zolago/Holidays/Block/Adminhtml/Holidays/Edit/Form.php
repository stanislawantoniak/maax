<?php
class Zolago_Holidays_Block_Adminhtml_Holidays_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {  
        parent::__construct();
     
        $this->setId('zolago_holidays_form');
        $this->setTitle($this->__('Holiday Information'));
    }  
     
    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {  
        $model = Mage::registry('holiday');
     
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('zolagoholidays')->__('Holiday Information'),
            'class'     => 'fieldset-wide',
        ));
     
        if ($model->getId()) {
            $fieldset->addField('holiday_id', 'hidden', array(
                'name' => 'holiday_id',
            ));
        }  
     
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('zolagoholidays')->__('Name'),
            'title'     => Mage::helper('zolagoholidays')->__('Name'),
            'required'  => true,
        ));
		
		$fieldset->addField('country_id', 'select', array(
	       'name'          => 'country_id',
	       'label'         => Mage::helper('zolagoholidays')->__('Country'),
	       'values'        => Mage::getSingleton("adminhtml/system_config_source_country")->toOptionArray(),
	       'required'      => true,
		   'class'		   => "form-control"
	   ));
		
		$fieldset->addField('date', 'date', array(
				'name'      => 'date',
		        'label'     => Mage::helper('zolagoholidays')->__('Date'),
		        'format'    => 'dd/MM/yyyy',
		        'time'      => false,
		        'image'     => $this->getSkinUrl('images/grid-cal.gif'),
		        'required'  => true,
	    ));
     	
		$fieldset->addField('type', 'radios', array(
	          	'label'     => Mage::helper('zolagoholidays')->__('Type'),
	          	'name'      => 'type',
	          	'value'     => '1',
	          	'values'    => array(
	                array('value'=>'1','label'=> Mage::helper('zolagoholidays')->__('Fixed')),
	                array('value'=>'2','label'=> Mage::helper('zolagoholidays')->__('Movable')),
	           	),
	           	'required'  => false
        ));
		
		$fieldset->addField('exclude_from_pickup', 'checkbox', array(
				'name' => 'exclude_from_pickup',
          		'label'     => Mage::helper('zolagoholidays')->__('Exclude from pickup'),
          		'checked' => true,
          		'value'  => 1
        ));
		
		$fieldset->addField('exclude_from_delivery', 'checkbox', array(
				'name' => 'exclude_from_delivery',
          		'label'     => Mage::helper('zolagoholidays')->__('Exclude from delivery'),
          		'checked' => true,
          		'value'  => '1'
        ));
		
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    }
}
