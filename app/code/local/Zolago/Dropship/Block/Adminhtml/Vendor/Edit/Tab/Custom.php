<?php

class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Custom extends Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Custom
{

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('custom', array(
            'legend'=>$hlp->__('Custom Vendor Information')
        ));

        $fieldset->addField('custom_data_combined', 'textarea', array(
            'name'      => 'custom_data_combined',
            'label'     => $hlp->__('Custom Data'),
            'style'     => 'height:500px',
            'note'      => $this->__("Enter custom data for this vendor.<br/>Each part should start with:<br/><pre>===== part_name =====</pre><br/>Parts can be referenced from product template like this:<xmp><?php echo Mage::helper('udropship')->getVendor(\$_product)->getData('part_name')?></xmp>"
            ),
        ));

        /*
         * Vendor Info START
         */

        $fieldset = $form->addFieldset('vendor_form', array(
            'legend'=>$hlp->__('Vendor Info')
        ));

        $fieldset->addField('carrier_code', 'select', array(
            'name'      => 'carrier_code',
            'label'     => $hlp->__('Preferred Carrier'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => Mage::getSingleton('udropship/source')->setPath('carriers')->toOptionHash(true),
        ));

        $fieldset->addField('use_rates_fallback', 'select', array(
            'name'      => 'use_rates_fallback',
            'label'     => $hlp->__('Use Rates Fallback Chain'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => Mage::getSingleton('udropship/source')->setPath('yesno')->toOptionHash(true),
            'note'      => $hlp->__('Will try to find available estimate rate for dropship shipping methods in order <br>1. Estimate Carrier <br>2. Override Carrier <br>3. Default Carrier'),
        ));

        $templates = Mage::getSingleton('adminhtml/system_config_source_email_template')->toOptionArray();
        $templates[0]['label'] = $hlp->__('Use Default Configuration');
        $fieldset->addField('email_template', 'select', array(
            'name'      => 'email_template',
            'label'     => $hlp->__('Notification Template'),
            'values'   => $templates,
        ));

        Mage::dispatchEvent('zolago_adminhtml_vendor_edit_custom_prepare_form_fieldset_vendor_info', array('block'=>$this, 'form'=>$form, 'id'=>$id));

        /*
         * Vendor Info END
         */

        if ($vendor) {
            if ($this->getRequest()->getParam('reg_id')) {
                $shipping = array();
                foreach ($vendor->getShippingMethods() as $sId=>$_s) {
                    foreach ($_s as $s) {
                        $shipping[$sId][] = array(
                            'on' => 1,
                            'est_carrier_code' => $s['est_carrier_code'],
                            'carrier_code' => $s['carrier_code'],
                        );
                    }
                }
                $vendor->setVendorShipping(Zend_Json::encode($shipping));
                $vendor->setSendConfirmationEmail(!Mage::getStoreConfigFlag('udropship/microsite/skip_confirmation'));
            }
            $form->setValues($vendor->getData());
        }

        return $this;
    }

}