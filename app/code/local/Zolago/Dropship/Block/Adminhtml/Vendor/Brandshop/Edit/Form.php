<?php
/**
 * Brandshop settings form
 */

class Zolago_Dropship_Block_Adminhtml_Vendor_Brandshop_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm()
    {
        $model = Mage::registry('vendor_brandshop');
        $vendorId = $model->getVendorId();
        $brandshopId = $model->getBrandshopId();
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        $brandshop = Mage::getModel('udropship/vendor')->load($brandshopId);
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/brandshopSave', array('vendor_id' => $model->getVendorId(),'brandshop_id'=>$model->getBrandshopId())),
            'method'    => 'post'
        ));
        
        $fieldset = $form->addFieldset('brandshop_settings', array('legend' => Mage::helper('zolagodropship')->__('Brandshop settings details'), 'class' => 'fieldset-wide'));
        $fieldset->addField('vendor_name', 'note', array(
            'label'     => Mage::helper('zolagodropship')->__('Vendor'),
            'text'      => '<a href="' . $this->getUrl('udropshipadmin/adminhtml_vendor/edit', array('id' => $vendorId)) . '" onclick="this.target=\'blank\'">' . $vendor->getVendorName() . '</a>'
        ));
        $fieldset->addField('brandshop_name', 'note', array(
            'label'     => Mage::helper('zolagodropship')->__('Brandshop'),
            'text'      => '<a href="' . $this->getUrl('udropshipadmin/adminhtml_vendor/edit', array('id' => $brandshopId)) . '" onclick="this.target=\'blank\'">' . $brandshop->getVendorName() . '</a>'
        ));
        $fieldset->addField('description', 'textarea', array(
            'label'     => Mage::helper('zolagodropship')->__('Vendor description at brandshop page'),
            'required'  => false,
            'name'      => 'description',
            'style'     => 'height:24em;',
        ));
        $yesNo = array (
            '0' => Mage::helper('zolagodropship')->__('No'),
            '1' => Mage::helper('zolagodropship')->__('Yes'),
        );
         $fieldset->addField('can_ask', 'select', array(
            'label'     => Mage::helper('zolagodropship')->__('Customer can ask'),
            'name'      => 'can_ask',
            'values'    => $yesNo,
        ));

        $fieldset->addField('can_add_product', 'select', array(
            'label'     => Mage::helper('zolagodropship')->__('Vendor can add products to brandshop'),
            'name'      => 'can_add_product',
            'values' 	=> $yesNo,
            )
        );
        $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
        
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
            'text'      => '<a href="' . $this->getUrl('udropshipadmin/adminhtml_vendor/edit', array('id' => $vendor->getId())) . '" onclick="this.target=\'blank\'">' . $vendor->getVendorName() . '</a>'
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