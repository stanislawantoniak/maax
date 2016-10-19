<?php

class ZolagoOs_OmniChannelVendorAskQuestion_Block_Adminhtml_Question_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $question = Mage::registry('question_data');
        $vendor = Mage::helper('udropship')->getVendor($question->getVendorId());
        $shipment = Mage::getModel('sales/order_shipment')->load($question->getShipmentId());
        $customer = Mage::getModel('customer/customer')->load($question->getCustomerId());
        $statuses = Mage::getSingleton('udqa/source')
            ->setPath('statuses')
            ->toOptionArray();

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'), 'ret' => Mage::registry('ret'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('question_details', array('legend' => Mage::helper('udqa')->__('Question Details'), 'class' => 'fieldset-wide'));

        $fieldset->addField('vendor_name', 'note', array(
            'label'     => Mage::helper('udqa')->__('Vendor'),
            'text'      => '<a href="' . $this->getUrl('zolagoosadmin/adminhtml_vendor/edit', array('id' => $vendor->getId())) . '" onclick="this.target=\'blank\'">' . $vendor->getVendorName() . '</a>'
        ));

        if ($question->getShipmentId()) {
            $fieldset->addField('shipment', 'note', array(
                'label'     => Mage::helper('udqa')->__('Shipment'),
                'text'      => sprintf('<a onclick="this.target=\'blank\'" href="%sshipment_id/%s/">#%s</a> for order <a onclick="this.target=\'blank\'" href="%sorder_id/%s/">#%s</a>', $this->getUrl('adminhtml/sales_shipment/view'), $question->getShipmentId(), $question->getShipmentIncrementId(), $this->getUrl('adminhtml/sales_order/view'), $question->getOrderId(), $question->getOrderIncrementId())
            ));
        }

        if ($question->getProductId()) {
            $fieldset->addField('product', 'note', array(
                'label'     => Mage::helper('udqa')->__('Product'),
                'text'      => '<a href="' . $this->getUrl('adminhtml/catalog_product/edit', array('id' => $question->getProductId())) . '" onclick="this.target=\'blank\'"> SKU: ' . $question->getProductSku() . ' NAME: ' . $question->getProductName() . '</a>'
            ));
        }

        if ($customer->getId()) {
            $customerText = Mage::helper('udqa')->__('<a href="%1$s" onclick="this.target=\'blank\'">%2$s %3$s</a> <a href="mailto:%4$s">(%4$s)</a>',
                $this->getUrl('*/customer/edit', array('id' => $customer->getId(), 'active_tab'=>'udqa')),
                $this->htmlEscape($customer->getFirstname()),
                $this->htmlEscape($customer->getLastname()),
                $this->htmlEscape($customer->getEmail()));
        } else {
            if (is_null($question->getCustomerId())) {
                $customerText = Mage::helper('udqa')->__('Guest');
            } elseif ($question->getCustomerId() == 0) {
                $customerText = Mage::helper('udqa')->__('Administrator');
            }
        }

        $fieldset->addField('customer', 'note', array(
            'label'     => Mage::helper('udqa')->__('Posted By'),
            'text'      => $customerText,
        ));

        $fieldset->addField('customer_name', 'text', array(
            'label'     => Mage::helper('udqa')->__('Name'),
            'required'  => true,
            'name'      => 'customer_name'
        ));

         $fieldset->addField('question_status', 'select', array(
            'label'     => Mage::helper('udqa')->__('Question Status'),
            'required'  => true,
            'name'      => 'question_status',
            'values'    => Mage::helper('udqa')->translateArray($statuses),
        ));

        $fieldset->addField('question_text', 'textarea', array(
            'label'     => Mage::helper('udqa')->__('Question Text'),
            'required'  => true,
            'name'      => 'question_text',
            'style'     => 'height:24em;',
        ));

        $fieldset->addField('answer_status', 'select', array(
            'label'     => Mage::helper('udqa')->__('Answer Status'),
            'required'  => true,
            'name'      => 'answer_status',
            'values'    => Mage::helper('udqa')->translateArray($statuses),
        ));

        $fieldset->addField('answer_text', 'textarea', array(
            'label'     => Mage::helper('udqa')->__('Answer Text'),
            'name'      => 'answer_text',
            'style'     => 'height:24em;',
        ));

        $form->setUseContainer(true);
        $form->setValues($question->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
