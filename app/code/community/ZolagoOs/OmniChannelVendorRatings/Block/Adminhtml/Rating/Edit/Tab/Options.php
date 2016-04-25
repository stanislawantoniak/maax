<?php

class ZolagoOs_OmniChannelVendorRatings_Block_Adminhtml_Rating_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('options_form', array('legend'=>Mage::helper('rating')->__('Assigned Options')));

        if (Mage::registry('rating_data')) {
            $collection = Mage::getModel('rating/rating_option')
                ->getResourceCollection()
                ->addRatingFilter(Mage::registry('rating_data')->getId())
                ->load();

            foreach( $collection->getItems() as $item ) {
                $fieldset->addField('option_code_' . $item->getId() , 'text', array(
                                        'label'     => Mage::helper('rating')->__('Option Label'),
                                        'required'  => true,
                                        'name'      => 'option_title[' . $item->getId() . ']',
                                        'value'     => $item->getCode(),
                                    )
                );
            }
        } 

        $this->setForm($form);
        return parent::_prepareForm();
    }

}
