<?php
/**
 * helper used in format vendor admin interface
 */
class Zolago_Dropship_Helper_Tabs extends Mage_Core_Helper_Abstract {

    protected $_additionalElementTypes = null;

    /**
     * attach field specified by key from config to fieldset
     * 
     * @param string $key
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     */     
    public function addKeyToFieldset($key,$fieldset) {
        $field = $this->keyToField($key);
        $type = $this->getAdditionalElementTypes();
        if (!empty($type[$field['type']])) {
            $fieldset->addType($field['type'],$type[$field['type']]);
        }
        $fieldset->addField($key,$field['type'],$field['params']);
    }
    /**
     * prepare field from config key
     *
     * @param string $key
     * @return array
     */

    public function keyToField($key) {
        $configKey = 'global/udropship/vendor/fields/'.$key;
        $node = Mage::getConfig()->getNode($configKey);
        return $this->nodeToField($node,$key);
    }
    /**
     * prepare field from config node
     *
     * @param Mage_Core_Model_Config_Element $node config node
     * @param string $code
     * @return array
     */
    public function nodeToField($node,$code) {
        $hlp = Mage::helper('udropship');
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
                $nodeArray = $node->asArray();
                $selector = isset($nodeArray['selector']) ? $nodeArray['selector'] : false;
                $field['params']['options'] = $source->toOptionHash($selector);
            }
            break;

        case 'date':
        case 'datetime':
            $field['params']['image'] = Mage::getDesign()->getSkinUrl('images/grid-cal.gif');
            $field['params']['input_format'] = Varien_Date::DATE_INTERNAL_FORMAT;
            $field['params']['format'] = Varien_Date::DATE_INTERNAL_FORMAT;
#Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            break;
        }
        
        return $field;
    }
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
        $this->_additionalElementTypes = array(
            'wysiwyg' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_wysiwyg'),
            'statement_po_type' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_statementPoType'),
            'payout_po_status_type' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_PayoutPoStatusType'),
            'notify_lowstock' => Mage::getConfig()->getBlockClassName('udropship/adminhtml_vendor_helper_form_notifyLowstock'),
        );
        }
        return $this;
    }
    public function getAdditionalElementTypes()
    {
        $this->_initAdditionalElementTypes();
        return $this->_additionalElementTypes;
    }
    public function addAdditionalElementType($code, $class)
    {
        $this->_initAdditionalElementTypes();
        $this->_additionalElementTypes[$code] = Mage::getConfig()->getBlockClassName($class);
        return $this;
    }

}