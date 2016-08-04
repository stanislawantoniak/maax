<?php

/**
 * Class Zolago_Freshmail_Block_Popup
 */
class Zolago_Freshmail_Block_Popup extends Snowdog_Freshmail_Block_Popup {
    protected function _prepareCustomFieldsHtml()
    {
        $form = new Varien_Data_Form();
        $fieldRenderer = new Snowdog_Freshmail_Model_Renderer();

        $config = Mage::getSingleton('snowfreshmail/config')->getCustomFieldMappings();
        if(!empty($config) && is_array($config)){
            foreach ($config as $map) {
                if (!$map['use_in_form']) {
                    continue;
                }

                $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', $map['source_field']);
                if ($attribute->isStatic() || !$attribute->getIsVisible()) {
                    continue;
                }

                $frontendModel = $attribute->getFrontend();
                $inputType = $frontendModel->getInputType();
                if ($inputType == 'date' || $inputType == 'datetime') {
                    $inputType = 'text';
                }

                $config = array(
                    'name' => $map['source_field'],
                    'label' => $frontendModel->getLabel(),
                );

                if ($attribute->usesSource()) {
                    if ($frontendModel->getSelectOptions()) {
                        $config['values'] = $frontendModel->getSelectOptions();
                    }
                }

                $form->addField($map['source_field'], $inputType, $config)->setRenderer($fieldRenderer);
            }
        }

        $form->setUseContainer(false);
        return $form->toHtml();
    }
}