<?php

class Zolago_DropshipMicrositePro_Block_Vendor_Register extends ZolagoOs_OmniChannelMicrositePro_Block_Vendor_Register
{
    protected function _beforeToHtml()
    {

        parent::_beforeToHtml();

        $hlp = Mage::helper('udropship');
        $sHlp = Mage::helper('udmspro');

        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('udmspro/vendor_register_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('udmspro/vendor_register_renderer_fieldsetElement')
        );

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fsIdx = 0;
        $columnsConfig = Mage::getStoreConfig('zossignup/form/fieldsets');
        if (!is_array($columnsConfig)) {
            $columnsConfig = Mage::helper('udropship')->unserialize($columnsConfig);
            if (is_array($columnsConfig)) {
            foreach ($columnsConfig as $fsConfig) {
            if (is_array($fsConfig)) {
                $requiredFields = (array)@$fsConfig['required_fields'];
                $fieldsExtra = (array)@$fsConfig['fields_extra'];
                $fields = array();
                foreach (array('top_columns','bottom_columns','left_columns','right_columns') as $colKey) {
                if (isset($fsConfig[$colKey]) && is_array($fsConfig[$colKey])) {
                    foreach ($fsConfig[$colKey] as $fieldCode) {
                        $field = Mage::helper('udmspro/protected')->getRegistrationField($fieldCode);
                        if (!empty($field)) {
                            switch ($colKey) {
                                case 'top_columns':
                                    $field['is_top'] = true;
                                    break;
                                case 'bottom_columns':
                                    $field['is_bottom'] = true;
                                    break;
                                case 'right_columns':
                                    $field['is_right'] = true;
                                    break;
                                default:
                                    $field['is_left'] = true;
                                    break;
                            }
                            if (in_array($fieldCode, $requiredFields)) {
                                $field['required'] = true;
                            } else {
                                $field['required'] = false;
                                if (!empty($field['class'])) {
                                    $field['class'] = str_replace('required-entry', '', $field['class']);
                                }
                            }
                            if (!empty($fieldsExtra[$fieldCode]['use_custom_label'])
                                && !empty($fieldsExtra[$fieldCode]['custom_label'])
                            ) {
                                $field['label'] = $fieldsExtra[$fieldCode]['custom_label'];
                            }
                            $fields[$fieldCode] = $field;
                        }
                    }
                }}

                if (!empty($fields)) {
                    $fsIdx++;
                    $fieldset = $form->addFieldset('group_fields'.$fsIdx,
                        array(
                            'legend'=>$fsConfig['title'],
                            'class'=>'fieldset-wide',
                    ));
                    $this->_addElementTypes($fieldset);
                    foreach ($fields as $field) {

                        if (!empty($field['input_renderer'])) {
                            $fieldset->addType($field['type'], $field['input_renderer']);
                        }
                        $formField = $fieldset->addField($field['id'], $field['type'], $field);
                        if (!empty($field['renderer'])) {
                            $formField->setRenderer($field['renderer']);
                        }
                        $formField->addClass('input-text');

                        if (!empty($field['required'])) {
                            $formField->addClass('required-entry');
                        }
//                        if (!empty($field['id'])) {
//                            $formField->addClass('required-entry');
//                        }


                        $formField->addClass('form-control');
                    }
                    $this->_prepareFieldsetColumns($fieldset);
                    $emptyForm = false;
                }
            }}}
        }

        $_data = Mage::getSingleton('udropship/session')->getRegistrationFormData(true);
        if ($_data) {
            $form->setValues($_data);
        }

        return $this;
    }

    protected function _prepareFieldsetColumns($fieldset)
    {
        $elements = $fieldset->getElements()->getIterator();
        reset($elements);
        $bottomElements = $topElements = $lcElements = $rcElements = array();
        while($element=current($elements)) {
            if ($element->getIsBottom()) {
                $bottomElements[] = $element->getId();
            } elseif ($element->getIsTop()) {
                $topElements[] = $element->getId();
            } elseif ($element->getIsRight()) {
                $rcElements[] = $element->getId();
            } else {
                $lcElements[] = $element->getId();
            }
            next($elements);
        }
        $fieldset->setTopColumn($topElements);
        $fieldset->setBottomColumn($bottomElements);
        $fieldset->setLeftColumn($lcElements);
        $fieldset->setRightColumn($rcElements);
        reset($elements);
        return $this;
    }

    protected $_additionalElementTypes = null;
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
    protected function _getAdditionalElementTypes()
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
    protected function _addElementTypes(Varien_Data_Form_Abstract $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }

    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        Varien_Profiler::start('TEST: ' . __METHOD__);
        if (is_null($defValue)) {
            $defValue = $this->getCountryId();
        }
        $cacheKey = 'DIRECTORY_COUNTRY_SELECT_STORE_' . Mage::app()->getStore()->getCode();
        if (Mage::app()->useCache('config') && $cache = Mage::app()->loadCache($cacheKey)) {
            $options = unserialize($cache);
        } else {
            $options = $this->getCountryCollection()->toOptionArray();
            if (Mage::app()->useCache('config')) {
                Mage::app()->saveCache(serialize($options), $cacheKey, array('config'));
            }
        }
        $html = $this->getLayout()->createBlock('core/html_select')
            ->setName($name)
            ->setId($id)
            ->setTitle(Mage::helper('directory')->__($title))
            ->setClass('validate-select form-control')
            ->setValue($defValue)
            ->setOptions($options)
            ->getHtml();

        Varien_Profiler::stop('TEST: ' . __METHOD__);
        return $html;
    }
}