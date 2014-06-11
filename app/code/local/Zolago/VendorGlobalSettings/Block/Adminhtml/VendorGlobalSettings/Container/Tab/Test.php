<?php

class Zolago_VendorGlobalSettings_Block_Adminhtml_VendorGlobalSettings_Container_Tab_Test extends Mage_Adminhtml_Block_Catalog_Form
{

    /**
     * Prepare attributes form
     *
     * @return null
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $helper = Mage::helper('zolagovendorglobalsettings');
		
    	$form = new Varien_Data_Form(array(
            'id'        => 'test_form',
            'action'    => $this->getUrl('*/*/save'),
            'method'    => 'post'
        ));
     
        $fieldset = $form->addFieldset('test_fieldset', array(
            'legend'    => $helper->__('Test')
        ));
		
		$fieldset->addField('test', 'text', array(
            'name'      => 'test',
            'label'     => Mage::helper('zolagoholidays')->__('Test'),
            'title'     => Mage::helper('zolagoholidays')->__('Test'),
            'required'  => true,
        ));
		
		$this->setForm($form);
    }
    
}
