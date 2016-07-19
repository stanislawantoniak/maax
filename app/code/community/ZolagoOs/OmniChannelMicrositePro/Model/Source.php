<?php
/**
  
 */

/**
* Currently not in use
*/
class ZolagoOs_OmniChannelMicrositePro_Model_Source extends ZolagoOs_OmniChannel_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('udropship');
        $sHlp = Mage::helper('udmspro');

        $selectorLabel = '* Please select';

        switch ($this->getPath()) {

        case 'cms_landing_page':
            $_options = Mage::getSingleton('adminhtml/system_config_source_cms_page')->toOptionArray();
            $options[-1] = $hlp->__('* Use config');
            foreach ($_options as $_opt) {
                $options[$_opt['value']] = $_opt['label'];
            }
            break;
        case 'billing_region_id':
        case 'region_id':
            $selectorLabel = 'Please select region, state or province';
            $options = array(
            );
            break;
        case 'billing_country_id':
        case 'country_id':
            $options = array(
            );
            break;

        default:
            Mage::throwException($hlp->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$hlp->__($selectorLabel)) + $options;
        }

        return $options;
    }

    protected $_vendorPreferences = array();
    public function getVendorPreferences($filterVisible=false)
    {
        if (!isset($this->_vendorPreferences[$filterVisible])) {
            $hlp = Mage::helper('udropship');

            $visible = Mage::getStoreConfig('zolagoos/vendor/visible_preferences');
            $visible = $visible ? explode(',', $visible) : false;

            $fieldsets = array();
            foreach (Mage::getConfig()->getNode('global/udropship/vendor/fieldsets')->children() as $code=>$node) {
                if ($node->modules && !$hlp->isModulesActive((string)$node->modules)) {
                    continue;
                }
                $fieldsets[$code] = array(
                    'position' => (int)$node->position,
                    'label' => (string)$node->legend,
                    'value' => array(),
                );
            }
            foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
                if (empty($fieldsets[(string)$node->fieldset]) || $node->is('disabled')) {
                    continue;
                }
                if ($node->modules && !$hlp->isModulesActive((string)$node->modules)) {
                    continue;
                }
                if ($filterVisible && $visible && !in_array($code, $visible)) {
                    continue;
                }
                $field = array(
                    'position' => (int)$node->position,
                    'label' => (string)$node->label,
                    'value' => $code,
                );
                $fieldsets[(string)$node->fieldset]['value'][] = $field;
            }
            uasort($fieldsets, array($hlp, 'usortByPosition'));
            foreach ($fieldsets as $k=>$v) {
                if (empty($v['value'])) {
                    continue;
                }
                uasort($v['value'], array($hlp, 'usortByPosition'));
            }
            $this->_vendorPreferences[$filterVisible] = $fieldsets;
        }
        return $this->_vendorPreferences[$filterVisible];
    }
}