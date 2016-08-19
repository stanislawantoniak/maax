<?php

/**
 * Class Zolago_Payment_Block_Adminhtml_Vendor_Payment_Edit_Form
 */
class Zolago_Adminhtml_Block_Sales_Transactions_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
                'legend' => Mage::helper('sales')->__('General'),
                'class' => 'fieldset-wide'
            )
        );
        $fieldset->addField('txn_amount', 'text', array(
            'label' => Mage::helper('sales')->__('Amount'),
            'required' => true,
            'name' => 'txn_amount',
            'style' => 'max-width:100px;',
        ));
        $fieldset->addField('allow_order', 'hidden', array(
            'name' => 'allow_order'
        ));

        $fieldset->addField('date', 'date', array(
            'label' => Mage::helper('sales')->__('Date'),
            'required' => true,
            'name' => 'date',
            'format' => 'yyyy-MM-dd',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'time' => false,
            'after_element_html' => "<br /><small>" . Mage::helper('sales')->__('Allowed format: yyyy-mm-dd') . "</small>",
        ));
        
        $fieldset->addField('order_id', 'select', array(
            'label' => Mage::helper('sales')->__('Order'),
            'required' => true,
            'name' => 'order_id',
            "options" => Mage::getSingleton('zolagoadminhtml/sales_transactions_source')
                ->setPath('orders_info')
                ->toOptionHash()
        ));

        $form->setUseContainer(true);
        $form->setValues($this->_getValues());
        $this->setForm($form);
        return parent::_prepareForm();

    }

    protected function _getValues()
    {
        $data = $this->_getModel()->getData();

        if (isset($data['cost'])) {
            $data['cost'] = number_format($data['cost'], 2, '.', '');
        }
        if(isset($data['bank_transfer_create_at'])){
            $data['date'] = $data['bank_transfer_create_at'];
        }
        $data['allow_order'] = Zolago_Adminhtml_Block_Sales_Transactions::ALLOW_SET_ORDER_FOR_EXISTING_TRANSACTIONS;

        return $data;
    }

    /**
     * @return Zolago_Pos_Model_Pos
     */
    protected function _getModel()
    {
        return Mage::registry('current_transaction');
    }
}