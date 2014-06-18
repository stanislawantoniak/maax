<?php
class Zolago_Rma_Block_Adminhtml_Rma_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('zolago_return_reasons_form');
        $this->setTitle($this->__('Return Reason Information'));
    }

    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('returnreason');
		
		$helper = Mage::helper('zolagorma');
		
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => $helper->__('Return Reason Information'),
            'class'     => 'fieldset-wide',
        ));

        if ($model->getId()) {
            $fieldset->addField('return_reason_id', 'hidden', array(
                'name' => 'return_reason_id',
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => $helper->__('Name'),
            'title'     => $helper->__('Name'),
            'required'  => true
        ));
		
		$fieldset->addField('auto_days', 'text', array(
            'name'      => 'auto_days',
            'label'     => $helper->__('Instant return days #'),
            'title'     => $helper->__('Instant return days #'),
            'required'  => true,
            'class'      => 'validate-number'
        ));
        
		$fieldset->addField('allowed_days', 'text', array(
            'name'      => 'allowed_days',
            'label'     => $helper->__('Acknowledged return days #'),
            'title'     => $helper->__('Acknowledged return days #'),
            'required'  => true,
            'class'      => 'validate-number'
        ));
		
		$fieldset->addField('message', 'text', array(
            'name'      => 'message',
            'label'     => $helper->__('Message'),
            'title'     => $helper->__('Message'),
            'required'  => true
        ));


        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}