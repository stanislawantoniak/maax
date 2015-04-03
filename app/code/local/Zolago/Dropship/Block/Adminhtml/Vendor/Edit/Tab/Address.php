<?php

class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Address extends Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_address');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $vendorData = $vendor->getData();

        $filtersFieldsets = array('vendor_preferences');
        $fieldsets = $this->doFieldsets($filtersFieldsets);

        // Filtering fields belong to vendor_info_moved
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            if ($node->is('disabled')) {
                continue;
            }elseif(isset($node->fieldset) && in_array($node->fieldset, $filtersFieldsets)) {

                $field = $this->doField($node, $code);

                if ($node->name && (string)$node->name != $code && !isset($vendorData[$code])) {
                    $vendorData[$code] = isset($vendorData[(string)$node->name]) ? $vendorData[(string)$node->name] : '';
                }

                $field = $this->processType($field, $node, $code);
                $fieldsets[(string)$node->fieldset]['fields'][$code] = $field;
            }
        }

        uasort($fieldsets, array($hlp, 'usortByPosition'));
        foreach ($fieldsets as $k=>$v) {
            if (empty($v['fields'])) {
                continue;
            }
            $fieldset = $form->addFieldset($k, $v['params']);
            $this->_addElementTypes($fieldset);
            uasort($v['fields'], array($hlp, 'usortByPosition'));
            foreach ($v['fields'] as $k1=>$v1) {
                $fieldset->addField($k1, $v1['type'], $v1['params']);
            }
        }


        $form->setValues($vendor->getData());
        $form->setValues($vendorData);

        return $this;
    }
}