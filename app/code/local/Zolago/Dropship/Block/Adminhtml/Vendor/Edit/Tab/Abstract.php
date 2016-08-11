<?php
/**
 * organize vendor tabs
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Edit_Tab_Abstract extends Mage_Adminhtml_Block_Widget_Form {
    
    protected $helper;

    
    /**
     * prepare fieldset and fields from xml settings
     */

    protected function _readFieldsetFromXml() {
        $key = $this->configKey;
        $vendor = Mage::registry('vendor_data');
        $hlp = Mage::helper('udropship');
        $id = $this->getRequest()->getParam('id');
        if (!$form = $this->getForm()) {
            $form = new Varien_Data_Form();
            $this->setForm($form);
        }

        if (!$vendor) {
            $vendor = Mage::getModel('udropship/vendor');
        }
        $vendorData = $vendor->getData();
        $source = Mage::getSingleton('udropship/source');

        $fieldsets = array();
        if (Mage::getConfig()->getNode('global/udropship/'.$key.'/fieldsets')) {
            foreach (Mage::getConfig()->getNode('global/udropship/'.$key.'/fieldsets')->children() as $code=>$node) {
                if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                        || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
                        || $node->is('hidden')
                   ) {
                    continue;
                }
                $fieldsets[$code] = array(
                                        'position' => (int)$node->position,
                                        'params' => array(
                                            'legend' => $hlp->__((string)$node->legend),
                                        ),
                                    );
            }
        }
        
        if (Mage::getConfig()->getNode('global/udropship/vendor/fields')) {
            $this->helper = Mage::helper('zolagodropship/tabs');
            foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
                if (empty($fieldsets[(string)$node->fieldset]) || $node->is('disabled')) {
                    continue;
                }
                if ($node->modules && !$hlp->isModulesActive((string)$node->modules)
                        || $node->hide_modules && $hlp->isModulesActive((string)$node->hide_modules)
                   ) {
                    continue;
                }
                if ($node->name && (string)$node->name != $code && !isset($vendorData[$code])) {
                    $vendorData[$code] = @$vendorData[(string)$node->name];
                }
                $field = $this->helper->nodeToField($node,$code);
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

        $form->setValues($vendorData);
    }
    protected function _prepareForm() {
        $this->_readFieldsetFromXml();
        return parent::_prepareForm();

    }
    
    protected function _getAdditionalElementTypes() {
        return $this->helper->getAdditionalElementTypes();
    }
}