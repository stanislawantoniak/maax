<?php

class Zolago_Dropship_Block_Vendor_Preferences extends ZolagoOs_OmniChannel_Block_Vendor_Preferences
{
    public function isOperatorMode() {
        return Mage::getSingleton('udropship/session')->isOperatorMode();
    }
    public function getFieldsets()
    {
        $hlp = Mage::helper('udropship');

        $visible = Mage::getStoreConfig('zolagoos/vendor/visible_preferences');
        $visible = $visible ? explode(',', $visible) : false;

        $fieldsets = array();
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code=>$node) {
            if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
                || $node->is('hidden')
            ) {
                continue;
            }
            $fieldsets[$code] = array(
                'position' => (int)$node->position,
                'legend' => (string)$node->legend,
            );
        }
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            if (empty($fieldsets[(string)$node->fieldset]) || $node->is('disabled')) {
                continue;
            }
            if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
            ) {
                continue;
            }
            if ($visible && !in_array($code, $visible)) {
                continue;
            }
			if($node->only_admin && !Mage::app()->getStore()->isAdmin()){
				continue;
			}
            $type = $node->type ? (string)$node->type : 'text';
            $type = ($type == 'wysiwyg' && !$hlp->isWysiwygAllowed()) ? 'textarea' : $type;

            $field = array(
                'position' => (int)$node->position,
                'type' => $type,
                'name' => $node->name ? (string)$node->name : $code,
                'label' => (string)$node->label,
                'class' => (string)$node->class,
                'note' => (string)$node->note,
            );
            switch ($type) {
            case 'statement_po_type': case 'payout_po_status_type': case 'notify_lowstock':
            case 'select': case 'multiselect': case 'checkboxes': case 'radios':
                $source = Mage::getSingleton($node->source_model ? (string)$node->source_model : 'udropship/source');
                if (is_callable(array($source, 'setPath'))) {
                    $source->setPath($node->source ? (string)$node->source : $code);
                }
                $field['options'] = $source->toOptionArray();
                if ($node->depend_fields) {
                    $field['depend_select'] = 1;
                    $field['field_config'] = array(
                        'depend_fields' => $node->depend_fields->asArray()
                    );
                }
                break;
            }
            $fieldsets[(string)$node->fieldset]['fields'][$code] = $field;
        }

        $fieldsets['account'] = array(
            'position' => 0,
            'legend' => 'Account Information',
            'fields' => array(
                'vendor_name' => array(
                    'position' => 1,
                    'name' => 'vendor_name',
                    'type' => 'text',
                    'label' => 'Vendor Name',
                ),
                'vendor_attn' => array(
                    'position' => 2,
                    'name' => 'vendor_attn',
                    'type' => 'text',
                    'label' => 'Attention To',
                ),
                'email' => array(
                    'position' => 3,
                    'name' => 'email',
                    'type' => 'text',
                    'label' => 'Email Address / Login',
                ),
                'password' => array(
                    'position' => 4,
                    'name' => 'password',
                    'type' => 'text',
                    'label' => 'Login Password',
                ),
                'telephone' => array(
                    'position' => 5,
                    'name' => 'telephone',
                    'type' => 'text',
                    'label' => 'Telephone',
                ),
            ),
        );

        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();

        $countryId = Mage::registry('vendor_data') ? Mage::registry('vendor_data')->getCountryId() : null;
        if (!$countryId) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }

        $regionCollection = Mage::getModel('directory/region')
            ->getCollection()
            ->addCountryFilter($countryId);

        $regions = $regionCollection->toOptionArray();

        if ($regions) {
            $regions[0]['label'] = $hlp->__('Please select state...');
        } else {
            $regions = array(array('value'=>'', 'label'=>''));
        }

        $fieldsets['shipping_origin'] = array(
            'position' => 1,
            'legend' => 'Shipping Origin Address',
            'fields' => array(
                'street' => array(
                    'position' => 1,
                    'name' => 'street',
                    'type' => 'textarea',
                    'label' => 'Street',
                ),
                'city' => array(
                    'position' => 2,
                    'name' => 'city',
                    'type' => 'text',
                    'label' => 'City',
                ),
                'zip' => array(
                    'position' => 3,
                    'name' => 'zip',
                    'type' => 'text',
                    'label' => 'Zip / Postal code',
                ),
                'country_id' => array(
                    'position' => 4,
                    'name' => 'country_id',
                    'type' => 'select',
                    'label' => 'Country',
                    'options' => $countries,
                ),
                'region_id' => array(
                    'position' => 5,
                    'name' => 'region_id',
                    'type' => 'select',
                    'label' => 'State',
                    'options' => $regions,
                ),
            ),
        );

        $fieldsets['billing_address'] = array(
            'position' => 2,
            'legend' => 'Billing Address',
            'fields' => array(
                'billing_use_shipping' => array(
                    'position' => -1,
                    'name' => 'billing_use_shipping',
                    'type' => 'select',
                    'label' => 'Same as Shipping',
                    'options' => Mage::getSingleton('udropship/source')->setPath('billing_use_shipping')->toOptionArray(),
                    'depend_select' => 1,
                    'field_config' => array(
                        'depend_fields' => array(
                            'billing_vendor_attn' => '0',
                            'billing_street' => '0',
                            'billing_city' => '0',
                            'billing_zip' => '0',
                            'billing_country_id' => '0',
                            'billing_region_id' => '0',
                            'billing_email' => '0',
                            'billing_telephone' => '0',
                            'billing_fax' => '0',
                        )
                    )
                ),
                'billing_vendor_attn' => array(
                    'position' => 0,
                    'name' => 'billing_vendor_attn',
                    'type' => 'text',
                    'label' => 'Attention To',
                    'note'  => 'Leave empty to use default'
                ),
                'billing_street' => array(
                    'position' => 1,
                    'name' => 'billing_street',
                    'type' => 'textarea',
                    'label' => 'Street',
                ),
                'billing_city' => array(
                    'position' => 2,
                    'name' => 'billing_city',
                    'type' => 'text',
                    'label' => 'City',
                ),
                'billing_zip' => array(
                    'position' => 3,
                    'name' => 'billing_zip',
                    'type' => 'text',
                    'label' => 'Zip / Postal code',
                ),
                'billing_country_id' => array(
                    'position' => 4,
                    'name' => 'billing_country_id',
                    'type' => 'select',
                    'label' => 'Country',
                    'options' => $countries,
                ),
                'billing_region_id' => array(
                    'position' => 5,
                    'name' => 'billing_region_id',
                    'type' => 'select',
                    'label' => 'State',
                    'options' => $regions,
                ),
                'billing_email' => array(
                    'position' => 6,
                    'name' => 'billing_email',
                    'type' => 'text',
                    'label' => 'Email',
                    'note'  => 'Leave empty to use default'
                ),
                'billing_telephone' => array(
                    'position' => 7,
                    'name' => 'billing_telephone',
                    'type' => 'text',
                    'label' => 'Telephone',
                    'note'  => 'Leave empty to use default'
                ),
                'billing_fax' => array(
                    'position' => 8,
                    'name' => 'billing_fax',
                    'type' => 'text',
                    'label' => 'Fax',
                    'note'  => 'Leave empty to use default'
                ),
            ),
        );

        Mage::dispatchEvent('udropship_vendor_front_preferences', array(
            'fieldsets'=>&$fieldsets
        ));

        uasort($fieldsets, array($hlp, 'usortByPosition'));
        foreach ($fieldsets as $k=>$v) {
            if (empty($v['fields'])) {
                continue;
            }
            uasort($v['fields'], array($hlp, 'usortByPosition'));
        }

        return $fieldsets;
    }

}