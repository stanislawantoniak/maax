<?php

class GH_Rewrite_Block_Adminhtml_Ghrewrite_Csv_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {

        /** @var GH_Rewrite_Helper_Data $rewriteHelper */
        $rewriteHelper = Mage::helper('ghrewrite');

        $form = new Varien_Data_Form(array(
                'id'      => 'edit_form',
                'action'  => $this->getUrl('*/*/savecsv', array('id' => $this->getRequest()->getParam('id'))),
                'method'  => 'post',
                'enctype' => 'multipart/form-data'
            )
        );

        $this->setForm($form);
        $fieldset = $form->addFieldset('loadcsv_form', array('legend' => $rewriteHelper->__('CSV Loader')));

        $fieldset->addField('filename', 'image', array(
            'label' => $rewriteHelper->__('CSV File'),
            'required' => false,
            'name' => 'filename',
        ));

        return parent::_prepareForm();
    }
}
