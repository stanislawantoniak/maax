<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit_Form
 */
class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $request = $this->getRequest();

        $id = $request->get('id', NULL);


        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $id)),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('edit_form', array(
                'legend' => Mage::helper('zolagopayment')->__('Info'),
                'class' => 'fieldset-wide'
            )
        );

        $fieldset->addField('date', 'date', array(
            'label' => Mage::helper('zolagopayment')->__('Date'),
            'required' => true,
            'name' => 'date',
            'format' => 'yyyy-MM-dd',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'time' => false,
            'after_element_html' => Mage::helper('zolagopayment')->__('<small>Format (YYYY-MM-DD)</small>'),
        ));
        $fieldset->addField('cost', 'text', array(
            'label' => Mage::helper('zolagopayment')->__('Cost'),
            'required' => true,
            'name' => 'cost',
            'style'     => 'max-width:100px;',
        ));
        $fieldset->addField('vendor_id', 'select', array(
            'label' => Mage::helper('zolagopayment')->__('Vendor'),
            'required' => true,
            'name' => 'vendor_id',
            "options" => Mage::getSingleton('zolagodropship/source')->setPath('vendors')->toOptionHash()
        ));
        $fieldset->addField('comment', 'textarea', array(
            'label' => Mage::helper('zolagopayment')->__('Comment'),
            'name' => 'comment',
        ));

        $form->setUseContainer(true);
        $form->setValues($this->_getValues());
        $this->setForm($form);
        return parent::_prepareForm();

    }

    protected function _getValues()
    {
        return $this->_getModel()->getData();
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _getModel()
    {
        return Mage::registry('zolagopayment_current_payment');
    }
}