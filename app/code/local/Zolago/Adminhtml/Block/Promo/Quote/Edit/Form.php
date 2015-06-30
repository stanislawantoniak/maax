<?php
/**
 * multipart/form-data in form
 */
class Zolago_Adminhtml_Block_Promo_Quote_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    public function __construct()
    {
        parent::__construct();
        $this->setId('promo_quote_form');
        $this->setTitle(Mage::helper('salesrule')->__('Rule Information'));
    }
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post','enctype'=>'multipart/form-data'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}