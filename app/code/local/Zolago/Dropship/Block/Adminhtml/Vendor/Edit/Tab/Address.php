<?php

class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Address extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldsets = array();
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code => $node) {
            if ( $code == 'vendor_preferences') {
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
            }elseif(isset($node->fieldset) && ($node->fieldset == 'vendor_preferences')) {

                $type = $node->type ? (string)$node->type : 'text';
                $field = array(
                    'position' => (float)$node->position,
                    'type' => $type,
                    'params' => array(
                        'name' => $node->name ? (string)$node->name : $code,
                        'class' => (string)$node->class,
                        'label' => $hlp->__((string)$node->label),
                        'note' => $hlp->__((string)$node->note),
                        'field_config' => $node
                    ),
                );
                if ($node->name && (string)$node->name != $code && !isset($vendorData[$code])) {
                    $vendorData[$code] = isset($vendorData[(string)$node->name]) ? $vendorData[(string)$node->name] : '';
                }
                if ($node->frontend_model) {
                    $field['type'] = $code;
                    $this->addAdditionalElementType($code, $node->frontend_model);
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

        return parent::_prepareForm();
    }

}