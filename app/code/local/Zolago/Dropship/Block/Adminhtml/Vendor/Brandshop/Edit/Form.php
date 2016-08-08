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
            'text'      => '<a href="' . $this->getUrl('zolagoosadmin/adminhtml_vendor/edit', array('id' => $vendorId)) . '" onclick="this.target=\'blank\'">' . $vendor->getVendorName() . '</a>'
        ));
        $fieldset->addField('brandshop_name', 'note', array(
            'label'     => Mage::helper('zolagodropship')->__('Brandshop'),
            'text'      => '<a href="' . $this->getUrl('zolagoosadmin/adminhtml_vendor/edit', array('id' => $brandshopId)) . '" onclick="this.target=\'blank\'">' . $brandshop->getVendorName() . '</a>'
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

        $indexByGoogleOptions = Mage::getSingleton('zolagodropship/source')
            ->setPath('vendorindexbygoogle')
            ->toOptionHash();

        $fieldset->addField('index_by_google', 'select', array(
                'label'     => Mage::helper('zolagodropship')->__('Index By Google'),
                'name'      => 'index_by_google',
                'values' 	=> $indexByGoogleOptions
            )
        );

        $form->setUseContainer(true);
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
        
    }
}