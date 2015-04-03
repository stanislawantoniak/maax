<?php
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences extends Unirgy_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Preferences
{
    protected function _prepareForm()
    {
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor');
        }
        $vendorData = $vendor->getData();

        $dontShow = array('vendor_info_moved','marketing','vendor_preferences','dhl','orbaups','rma');
        $fieldsets = $this->doFieldsets();

        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            if (in_array($node->fieldset, $dontShow)) {
                continue;
            }
            if (empty($fieldsets[(string)$node->fieldset]) || $node->is('disabled')) {
                continue;
            }
            if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
            ) {
                continue;
            }
            $field = $this->doField($node, $code);

            if ($node->name && (string)$node->name != $code && !isset($vendorData[$code])) {
                $vendorData[$code] = isset($vendorData[(string)$node->name]) ? $vendorData[(string)$node->name] : '';
            }

            $field = $this->processType($field, $node, $code);
            $fieldsets[(string)$node->fieldset]['fields'][$code] = $field;
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

        $form->setValues($vendorData);

        return $this;
    }

    public function doField($node, $code) {
        $hlp = Mage::helper('udropship');
        $field = array(
            'position' => (float)$node->position,
            'type' => $node->type ? (string)$node->type : 'text',
            'params' => array(
                'name' => $node->name ? (string)$node->name : $code,
                'class' => (string)$node->class,
                'label' => $hlp->__((string)$node->label),
                'note' => $hlp->__((string)$node->note),
                'field_config' => $node
            ),
        );
        if ($node->frontend_model) {
            $field['type'] = $code;
            $this->addAdditionalElementType($code, $node->frontend_model);
        }
        return $field;
    }

    public function doFieldsets($filters = array()) {

        $hlp = Mage::helper('udropship');
        $fieldsets = array();

        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code => $node) {
            if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
                || $node->is('hidden')
            ) {
                continue;
            }
            if (in_array($code, $filters) || empty($filters)) {
                $fieldsets[$code] = array(
                    'position' => (int)$node->position,
                    'params' => array(
                        'legend' => $hlp->__((string)$node->legend),
                    ),
                );
            }
        }
        return $fieldsets;
    }

    public function processType($field, $node, $code) {
        $type = $field['type'];
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
        return $field;
    }
}