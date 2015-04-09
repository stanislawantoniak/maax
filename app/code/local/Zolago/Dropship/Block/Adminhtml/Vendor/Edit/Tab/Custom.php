<?php
/**
 * override custom tab
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Custom extends Mage_Adminhtml_Block_Widget_Form {
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_custom');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

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


        $fieldset->addType('vacation_mode', Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_dependSelect'));
        $fieldset->addField('vacation_mode', 'vacation_mode', array(
                            'name'      => 'vacation_mode',
                            'label'     => $hlp->__('Vacation Mode'),
                            'options'   => Mage::getSingleton('udvacation/source')->setPath('vacation_mode')->toOptionHash(),
                            'field_config' => array(
                                'depend_fields' => array(
                                    'vacation_end' => '1,2',
                                )
                            )
                        ));
        $fieldset->addField('vacation_end', 'date', array(
                            'name'      => 'vacation_end',
                            'image' => Mage::getDesign()->getSkinUrl('images/grid-cal.gif'),
                            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                            'format' => Varien_Date::DATE_INTERNAL_FORMAT,
                            'label'     => $hlp->__('Vacation Ends At'),
                        ));



        $fieldset->addField('custom_data_combined', 'textarea', array(
                                'name'      => 'custom_data_combined',
                                'label'     => $hlp->__('Custom Data'),
                                'style'     => 'height:500px',
                                'note'      => $this->__("
                                        Enter custom data for this vendor.<br/>
                                        Each part should start with:<br/>
                                        <pre>===== part_name =====</pre><br/>
                                        Parts can be referenced from product template like this:
                                        <xmp>
                                        <?php echo Mage::helper('udropship')
                                        ->getVendor(\$_product)
                                        ->getData('part_name')?>
                                        </xmp>
                                        "
                                                        ),
                            ));
        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        return parent::_prepareForm();
    }

}