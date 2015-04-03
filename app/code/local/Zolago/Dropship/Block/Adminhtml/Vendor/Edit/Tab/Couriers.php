<?php

class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Couriers extends Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('vendor_couriers');
    }

    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $vendorData = $vendor->getData();

        $fieldsets = array();
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code => $node) {
            if (
                $code == 'dhl' ||
                $code == 'orbaups' ||
                $code == 'rma'
            ) {
                $fieldsets[$code] = array(
                    'position' => (int)$node->position,
                    'params' => array(
                        'legend' => $hlp->__((string)$node->legend),
                    ),
                );
            }
        }

        // Filtering fields belong to vendor_info_moved
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            if ($node->is('disabled')) {
                continue;
            }elseif(isset($node->fieldset) && (
                    $node->fieldset == 'dhl' ||
                    $node->fieldset == 'orbaups' ||
                    $node->fieldset == 'rma'
                )) {

                $field = $this->doField($node, $code);
                $type = $field['type'];

                if ($node->name && (string)$node->name != $code && !isset($vendorData[$code])) {
                    $vendorData[$code] = isset($vendorData[(string)$node->name]) ? $vendorData[(string)$node->name] : '';
                }

                switch ($type) {
                    case 'statement_po_type':
                    case 'payout_po_status_type':
                    case 'notify_lowstock':
                    case 'select':
                    case 'multiselect':
                    case 'checkboxes':
                    case 'radios':
                        $source = Mage::getSingleton($node->source_model ? (string)$node->source_model : 'udropship/source');
                        if (is_callable(array($source, 'setPath'))) {
                            $source->setPath($node->source ? (string)$node->source : $code);
                        }
                        if (in_array($type, array('multiselect', 'checkboxes', 'radios')) || !is_callable(array($source, 'toOptionHash'))) {
                            $field['params']['values'] = $source->toOptionArray();
                        } else {
                            $field['params']['options'] = $source->toOptionHash();
                        }
                        break;
                    case 'date': case 'datetime':
                    $field['params']['image'] = $this->getSkinUrl('images/grid-cal.gif');
                    $field['params']['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
                    $field['params']['format'] = Varien_Date::DATE_INTERNAL_FORMAT;
                    break;
                }
                $fieldsets[(string)$node->fieldset]['fields'][$code] = $field;
            }
        }

        foreach ($fieldsets as $k=>$v) {
            if (empty($v['fields'])) {
                continue;
            }
            $fieldset = $form->addFieldset($k, $v['params']);
            $this->_addElementTypes($fieldset);
            foreach ($v['fields'] as $k1=>$v1) {
                $fieldset->addField($k1, $v1['type'], $v1['params']);
            }
        }


        $form->setValues($vendor->getData());
        $form->setValues($vendorData);

        return $this;
    }
}